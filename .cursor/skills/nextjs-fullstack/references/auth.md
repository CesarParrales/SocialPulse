# Auth con Auth.js (NextAuth v5) y Middleware

## Setup Completo

```typescript
// lib/auth.ts — configuración central de Auth.js
import NextAuth from 'next-auth';
import Credentials from 'next-auth/providers/credentials';
import Google from 'next-auth/providers/google';
import { PrismaAdapter } from '@auth/prisma-adapter';
import { prisma } from '@/lib/db';
import bcrypt from 'bcryptjs';

export const { handlers, auth, signIn, signOut } = NextAuth({
  adapter: PrismaAdapter(prisma),

  providers: [
    Google({
      clientId: process.env.GOOGLE_CLIENT_ID,
      clientSecret: process.env.GOOGLE_CLIENT_SECRET,
    }),

    Credentials({
      credentials: {
        email:    { label: 'Email', type: 'email' },
        password: { label: 'Password', type: 'password' },
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) return null;

        const user = await prisma.user.findUnique({
          where: { email: String(credentials.email) },
        });

        if (!user?.passwordHash) return null;

        const isValid = await bcrypt.compare(
          String(credentials.password),
          user.passwordHash
        );

        if (!isValid) return null;

        return {
          id: user.id,
          email: user.email,
          name: user.name,
          role: user.role,
        };
      },
    }),
  ],

  session: { strategy: 'jwt' },

  callbacks: {
    // Agregar campos custom al JWT
    async jwt({ token, user }) {
      if (user) {
        token.id   = user.id;
        token.role = user.role;
      }
      return token;
    },

    // Exponer campos custom en session
    async session({ session, token }) {
      if (token) {
        session.user.id   = token.id as string;
        session.user.role = token.role as string;
      }
      return session;
    },
  },

  pages: {
    signIn:  '/login',
    error:   '/login',
    signOut: '/login',
  },
});

// Extender tipos de TypeScript
declare module 'next-auth' {
  interface User {
    role: string;
  }
  interface Session {
    user: {
      id: string;
      role: string;
      email: string;
      name: string;
    };
  }
}

// app/api/auth/[...nextauth]/route.ts
import { handlers } from '@/lib/auth';
export const { GET, POST } = handlers;
```

---

## Middleware — Proteger Rutas

```typescript
// middleware.ts (en la raíz del proyecto, no en src)
import { auth } from '@/lib/auth';
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export default auth(function middleware(req) {
  const session = req.auth;
  const { pathname } = req.nextUrl;

  // Rutas que requieren auth
  const protectedRoutes = ['/dashboard', '/orders', '/settings', '/admin'];
  const isProtected = protectedRoutes.some(r => pathname.startsWith(r));

  // Rutas solo para no-autenticados
  const authRoutes = ['/login', '/register'];
  const isAuthRoute = authRoutes.includes(pathname);

  if (isProtected && !session) {
    // Guardar URL destino para redirigir después del login
    const loginUrl = new URL('/login', req.url);
    loginUrl.searchParams.set('callbackUrl', pathname);
    return NextResponse.redirect(loginUrl);
  }

  if (isAuthRoute && session) {
    // Ya autenticado — redirigir al dashboard
    return NextResponse.redirect(new URL('/dashboard', req.url));
  }

  // Proteger rutas de admin
  if (pathname.startsWith('/admin') && session?.user.role !== 'ADMIN') {
    return NextResponse.redirect(new URL('/dashboard', req.url));
  }

  return NextResponse.next();
});

// Configurar en qué rutas corre el middleware
export const config = {
  matcher: [
    // Excluir archivos estáticos y API routes de next-auth
    '/((?!_next/static|_next/image|favicon.ico|api/auth).*)',
  ],
};
```

---

## Uso de Session en Componentes

```typescript
// Server Component — auth() de next-auth
import { auth } from '@/lib/auth';
import { redirect } from 'next/navigation';

async function ProfilePage() {
  const session = await auth();

  if (!session) redirect('/login');

  return (
    <div>
      <h1>Welcome, {session.user.name}</h1>
      {session.user.role === 'ADMIN' && <AdminPanel />}
    </div>
  );
}

// Client Component — useSession de next-auth/react
'use client';
import { useSession } from 'next-auth/react';

function UserAvatar() {
  const { data: session, status } = useSession();

  if (status === 'loading') return <AvatarSkeleton />;
  if (!session) return <LoginButton />;

  return (
    <img src={session.user.image ?? '/default-avatar.png'} alt={session.user.name} />
  );
}

// Providers en layout
// app/layout.tsx — SessionProvider para Client Components
import { SessionProvider } from 'next-auth/react';

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const session = await auth();
  return (
    <html>
      <body>
        <SessionProvider session={session}>
          {children}
        </SessionProvider>
      </body>
    </html>
  );
}
```

---

## Login Page con Server Action

```typescript
// app/login/page.tsx
import { LoginForm } from './LoginForm';

export default function LoginPage({
  searchParams,
}: {
  searchParams: { callbackUrl?: string; error?: string };
}) {
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="w-full max-w-md">
        <h1 className="text-2xl font-bold mb-6">Sign In</h1>

        {searchParams.error && (
          <div className="mb-4 p-3 bg-red-50 text-red-700 rounded">
            Invalid email or password
          </div>
        )}

        <LoginForm callbackUrl={searchParams.callbackUrl ?? '/dashboard'} />

        <div className="mt-6 text-center">
          <SocialLoginButtons />
        </div>
      </div>
    </div>
  );
}

// app/login/LoginForm.tsx
'use client';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';

function LoginForm({ callbackUrl }: { callbackUrl: string }) {
  const router = useRouter();
  const { register, handleSubmit, setError, formState: { errors, isSubmitting } } = useForm();

  const onSubmit = async (data: { email: string; password: string }) => {
    const result = await signIn('credentials', {
      email: data.email,
      password: data.password,
      redirect: false,  // manejar redirect manualmente
    });

    if (result?.error) {
      setError('root', { message: 'Invalid email or password' });
      return;
    }

    router.push(callbackUrl);
    router.refresh(); // refrescar Server Components con nueva sesión
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      {errors.root && (
        <p className="text-sm text-red-600">{errors.root.message}</p>
      )}

      <input type="email" placeholder="Email" {...register('email', { required: true })} />
      <input type="password" placeholder="Password" {...register('password', { required: true })} />

      <button type="submit" disabled={isSubmitting} className="w-full btn-primary">
        {isSubmitting ? 'Signing in...' : 'Sign In'}
      </button>
    </form>
  );
}
```
