# Hooks — Patrones y Custom Hooks

## Reglas que Siempre Aplican

```
1. Solo en componentes React o custom hooks (no en funciones regulares)
2. Solo en el nivel superior (no dentro de if, loops, callbacks)
3. Custom hook = función que empieza con "use" y puede contener otros hooks
4. Un custom hook por responsabilidad — no mega-hooks que hacen todo
5. Retornar tupla [value, setter] o objeto { data, actions } según complejidad
```

---

## useState — Patrones Correctos

```typescript
// Estado derivado — NO duplicar estado calculable
// ❌ MAL: dos estados que deben estar sincronizados
const [items, setItems] = useState<Item[]>([]);
const [total, setTotal] = useState(0);  // duplicado — puede desincronizarse

// ✅ BIEN: derivar en render
const [items, setItems] = useState<Item[]>([]);
const total = items.reduce((sum, item) => sum + item.price, 0); // siempre consistente

// Estado complejo — objeto o useReducer
// ❌ MAL: múltiples useState relacionados
const [isLoading, setIsLoading] = useState(false);
const [error, setError] = useState<string | null>(null);
const [data, setData] = useState<User | null>(null);
// Problema: pueden estar en estados imposibles (isLoading=true + data=User)

// ✅ BIEN: discriminated union
type UserState =
  | { status: 'idle' }
  | { status: 'loading' }
  | { status: 'success'; user: User }
  | { status: 'error'; message: string };

const [state, setState] = useState<UserState>({ status: 'idle' });
// Imposible tener loading=true y data al mismo tiempo

// Lazy initialization — para estado costoso de calcular
const [filter, setFilter] = useState(() => {
  // Solo se ejecuta en mount, no en cada render
  return JSON.parse(localStorage.getItem('savedFilter') ?? '{}');
});
```

---

## useReducer — Para Estado Complejo

```typescript
type CartAction =
  | { type: 'ADD_ITEM'; product: Product; quantity: number }
  | { type: 'REMOVE_ITEM'; productId: string }
  | { type: 'UPDATE_QUANTITY'; productId: string; quantity: number }
  | { type: 'CLEAR' }
  | { type: 'APPLY_COUPON'; discount: number };

interface CartState {
  items: CartItem[];
  couponDiscount: number;
}

function cartReducer(state: CartState, action: CartAction): CartState {
  switch (action.type) {
    case 'ADD_ITEM': {
      const existing = state.items.find(i => i.productId === action.product.id);
      if (existing) {
        return {
          ...state,
          items: state.items.map(item =>
            item.productId === action.product.id
              ? { ...item, quantity: item.quantity + action.quantity }
              : item
          ),
        };
      }
      return {
        ...state,
        items: [...state.items, {
          productId: action.product.id,
          name: action.product.name,
          price: action.product.price,
          quantity: action.quantity,
        }],
      };
    }
    case 'REMOVE_ITEM':
      return {
        ...state,
        items: state.items.filter(i => i.productId !== action.productId),
      };
    case 'CLEAR':
      return { items: [], couponDiscount: 0 };
    default:
      return state;
  }
}

function useCart() {
  const [state, dispatch] = useReducer(cartReducer, { items: [], couponDiscount: 0 });

  // Acciones con nombres descriptivos — no exponer dispatch directamente
  return {
    items: state.items,
    total: state.items.reduce((sum, i) => sum + i.price * i.quantity, 0),
    addItem:        (product: Product, quantity = 1) =>
      dispatch({ type: 'ADD_ITEM', product, quantity }),
    removeItem:     (productId: string) =>
      dispatch({ type: 'REMOVE_ITEM', productId }),
    updateQuantity: (productId: string, quantity: number) =>
      dispatch({ type: 'UPDATE_QUANTITY', productId, quantity }),
    clear:          () => dispatch({ type: 'CLEAR' }),
  };
}
```

---

## useEffect — Cuándo y Cómo

```typescript
// Regla: useEffect para sincronizar con sistemas externos
// NO para lógica de negocio que puede ser síncrona

// ✅ Casos válidos para useEffect:
// - Suscripciones (WebSocket, EventEmitter)
// - Animaciones imperativas
// - APIs del browser (título de página, focus, scroll)
// - Timers/intervals

// ❌ NO usar useEffect para:
// - Derivar estado de props (calcular en render o useMemo)
// - Fetch al mount (usar React Query)
// - Resetear estado cuando cambian props (useReducer o key prop)

// Cleanup obligatorio para suscripciones
function useWindowSize() {
  const [size, setSize] = useState({
    width: window.innerWidth,
    height: window.innerHeight,
  });

  useEffect(() => {
    const handler = () => setSize({
      width: window.innerWidth,
      height: window.innerHeight,
    });

    window.addEventListener('resize', handler);
    return () => window.removeEventListener('resize', handler); // cleanup
  }, []); // [] = solo montar/desmontar

  return size;
}

// useEffect con dependencias explícitas
function useDocumentTitle(title: string) {
  useEffect(() => {
    const prev = document.title;
    document.title = title;
    return () => { document.title = prev; }; // restaurar al desmontar
  }, [title]); // re-ejecutar solo cuando title cambia
}
```

---

## Custom Hooks — Biblioteca de Referencia

```typescript
// useDebounce — retrasar valor para búsqueda
function useDebounce<T>(value: T, delay = 300): T {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedValue(value), delay);
    return () => clearTimeout(timer);
  }, [value, delay]);

  return debouncedValue;
}

// Uso
const [search, setSearch] = useState('');
const debouncedSearch = useDebounce(search, 400);
// Usar debouncedSearch en la query — no search directamente

// ─────────────────────────────────────────

// useLocalStorage — persistir estado
function useLocalStorage<T>(key: string, initialValue: T) {
  const [value, setValue] = useState<T>(() => {
    try {
      const item = window.localStorage.getItem(key);
      return item ? JSON.parse(item) : initialValue;
    } catch {
      return initialValue;
    }
  });

  const set = (val: T | ((prev: T) => T)) => {
    const newVal = val instanceof Function ? val(value) : val;
    setValue(newVal);
    window.localStorage.setItem(key, JSON.stringify(newVal));
  };

  const remove = () => {
    setValue(initialValue);
    window.localStorage.removeItem(key);
  };

  return [value, set, remove] as const;
}

// ─────────────────────────────────────────

// useIntersectionObserver — lazy loading y animaciones on-scroll
function useIntersectionObserver(
  options: IntersectionObserverInit = {}
): [React.RefCallback<Element>, boolean] {
  const [isVisible, setIsVisible] = useState(false);
  const observerRef = useRef<IntersectionObserver | null>(null);

  const ref = useCallback((node: Element | null) => {
    if (observerRef.current) observerRef.current.disconnect();
    if (!node) return;

    observerRef.current = new IntersectionObserver(([entry]) => {
      setIsVisible(entry.isIntersecting);
    }, options);

    observerRef.current.observe(node);
  }, []);

  return [ref, isVisible];
}

// Uso
function LazySection({ children }: { children: React.ReactNode }) {
  const [ref, isVisible] = useIntersectionObserver({ threshold: 0.1 });
  return (
    <div
      ref={ref}
      className={cn('transition-opacity duration-500',
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
      )}
    >
      {isVisible && children}
    </div>
  );
}

// ─────────────────────────────────────────

// useAsync — manejo genérico de operaciones async
function useAsync<T, Args extends unknown[]>(
  asyncFn: (...args: Args) => Promise<T>
) {
  const [state, setState] = useState<AsyncState<T>>({ status: 'idle' });

  const execute = useCallback(async (...args: Args) => {
    setState({ status: 'loading' });
    try {
      const data = await asyncFn(...args);
      setState({ status: 'success', data });
      return data;
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Unknown error';
      setState({ status: 'error', error: message });
      throw error;
    }
  }, [asyncFn]);

  const reset = () => setState({ status: 'idle' });

  return { ...state, execute, reset };
}

// Uso para operaciones únicas (no fetch continuo — eso es React Query)
function CancelOrderButton({ orderId }: { orderId: string }) {
  const { status, execute } = useAsync(cancelOrder);

  return (
    <button
      onClick={() => execute(orderId)}
      disabled={status === 'loading'}
    >
      {status === 'loading' ? 'Cancelling...' : 'Cancel Order'}
    </button>
  );
}

// ─────────────────────────────────────────

// useKeyPress — atajos de teclado
function useKeyPress(
  key: string,
  callback: (e: KeyboardEvent) => void,
  options: { ctrl?: boolean; meta?: boolean; shift?: boolean } = {}
) {
  useEffect(() => {
    const handler = (e: KeyboardEvent) => {
      if (e.key !== key) return;
      if (options.ctrl && !e.ctrlKey) return;
      if (options.meta && !e.metaKey) return;
      if (options.shift && !e.shiftKey) return;
      callback(e);
    };

    document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [key, callback, options.ctrl, options.meta, options.shift]);
}

// ─────────────────────────────────────────

// useOnClickOutside — cerrar dropdowns/modals
function useOnClickOutside(
  ref: React.RefObject<HTMLElement>,
  handler: () => void
) {
  useEffect(() => {
    const listener = (e: MouseEvent | TouchEvent) => {
      if (!ref.current || ref.current.contains(e.target as Node)) return;
      handler();
    };

    document.addEventListener('mousedown', listener);
    document.addEventListener('touchstart', listener);
    return () => {
      document.removeEventListener('mousedown', listener);
      document.removeEventListener('touchstart', listener);
    };
  }, [ref, handler]);
}
```
