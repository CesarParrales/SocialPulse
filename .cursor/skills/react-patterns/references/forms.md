# Formularios — React Hook Form + Zod

## Por Qué Esta Combinación

```
React Hook Form: performance — no re-render en cada keystroke
Zod:            schemas tipados — validación compartida con backend
Resultado:      formularios con TypeScript end-to-end, sin estado manual
```

---

## Setup y Patrón Base

```typescript
// schemas/order.schema.ts — compartir con backend si TypeScript
import { z } from 'zod';

export const createOrderSchema = z.object({
  items: z.array(
    z.object({
      productId: z.string().min(1, 'Product is required'),
      quantity: z.number().int().min(1, 'Minimum 1').max(100, 'Maximum 100'),
    })
  ).min(1, 'At least one item is required'),

  shippingAddress: z.string()
    .min(10, 'Address is too short')
    .max(500, 'Address is too long'),

  couponCode: z.string()
    .toUpperCase()
    .optional()
    .or(z.literal('')),

  notes: z.string().max(1000, 'Max 1000 characters').optional(),
});

// Inferir tipo TypeScript del schema — una sola fuente de verdad
export type CreateOrderFormValues = z.infer<typeof createOrderSchema>;
```

---

## Formulario Completo

```typescript
// features/orders/components/CreateOrderForm.tsx
import { useForm, useFieldArray, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';

function CreateOrderForm({ onSuccess }: { onSuccess: (order: Order) => void }) {
  const createOrder = useCreateOrder();

  const {
    register,
    handleSubmit,
    control,
    watch,
    setValue,
    formState: { errors, isSubmitting, isDirty, isValid },
    reset,
  } = useForm<CreateOrderFormValues>({
    resolver: zodResolver(createOrderSchema),
    defaultValues: {
      items: [{ productId: '', quantity: 1 }],
      shippingAddress: '',
    },
    mode: 'onBlur',   // validar al perder foco (mejor UX que onChange)
  });

  // FieldArray para lista dinámica de items
  const { fields, append, remove } = useFieldArray({
    control,
    name: 'items',
  });

  const onSubmit = async (data: CreateOrderFormValues) => {
    try {
      const order = await createOrder.mutateAsync(data);
      reset();
      onSuccess(order.data);
    } catch (error) {
      // Errores de servidor → asignar a campos específicos
      if (isApiError(error) && error.errors) {
        Object.entries(error.errors).forEach(([field, messages]) => {
          setError(field as any, { message: messages[0] });
        });
      }
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} noValidate>
      {/* Items dinámicos */}
      <fieldset>
        <legend>Items</legend>

        {fields.map((field, index) => (
          <div key={field.id} className="flex gap-3 items-start mb-3">
            {/* Select de producto */}
            <Controller
              control={control}
              name={`items.${index}.productId`}
              render={({ field: { onChange, value }, fieldState: { error } }) => (
                <div className="flex-1">
                  <ProductSelector
                    value={value}
                    onChange={onChange}
                    error={error?.message}
                  />
                </div>
              )}
            />

            {/* Cantidad */}
            <div>
              <input
                type="number"
                {...register(`items.${index}.quantity`, { valueAsNumber: true })}
                min={1}
                max={100}
                className={cn('w-20 input', errors.items?.[index]?.quantity && 'input-error')}
              />
              {errors.items?.[index]?.quantity && (
                <p className="text-sm text-red-500">
                  {errors.items[index].quantity.message}
                </p>
              )}
            </div>

            {fields.length > 1 && (
              <button type="button" onClick={() => remove(index)}>
                Remove
              </button>
            )}
          </div>
        ))}

        {errors.items?.root && (
          <p className="text-sm text-red-500">{errors.items.root.message}</p>
        )}

        <button
          type="button"
          onClick={() => append({ productId: '', quantity: 1 })}
        >
          + Add Item
        </button>
      </fieldset>

      {/* Dirección */}
      <div>
        <label htmlFor="shippingAddress">Shipping Address</label>
        <textarea
          id="shippingAddress"
          {...register('shippingAddress')}
          rows={3}
          className={cn('textarea', errors.shippingAddress && 'textarea-error')}
        />
        {errors.shippingAddress && (
          <p className="text-sm text-red-500">{errors.shippingAddress.message}</p>
        )}
      </div>

      {/* Cupón */}
      <div>
        <label htmlFor="couponCode">Coupon Code (optional)</label>
        <input
          id="couponCode"
          {...register('couponCode')}
          placeholder="e.g. SAVE20"
          className="input uppercase"
        />
      </div>

      {/* Submit */}
      <button
        type="submit"
        disabled={isSubmitting || !isDirty || !isValid}
        className="btn-primary"
      >
        {isSubmitting ? 'Creating Order...' : 'Place Order'}
      </button>
    </form>
  );
}
```

---

## Componente de Input Reutilizable

```typescript
// shared/components/ui/FormField.tsx
interface FormFieldProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string;
  error?: string;
  hint?: string;
}

export const FormField = React.forwardRef<HTMLInputElement, FormFieldProps>(
  ({ label, error, hint, className, id, ...props }, ref) => {
    const fieldId = id ?? useId(); // ID único para accesibilidad

    return (
      <div className="space-y-1">
        <label
          htmlFor={fieldId}
          className="text-sm font-medium text-gray-700"
        >
          {label}
          {props.required && <span className="text-red-500 ml-1">*</span>}
        </label>

        <input
          ref={ref}
          id={fieldId}
          className={cn(
            'w-full rounded-md border px-3 py-2 text-sm transition-colors',
            'focus:outline-none focus:ring-2 focus:ring-blue-500',
            error
              ? 'border-red-500 focus:ring-red-500'
              : 'border-gray-300',
            className
          )}
          aria-invalid={!!error}
          aria-describedby={error ? `${fieldId}-error` : hint ? `${fieldId}-hint` : undefined}
          {...props}
        />

        {hint && !error && (
          <p id={`${fieldId}-hint`} className="text-xs text-gray-500">{hint}</p>
        )}

        {error && (
          <p id={`${fieldId}-error`} role="alert" className="text-xs text-red-500">
            {error}
          </p>
        )}
      </div>
    );
  }
);
FormField.displayName = 'FormField';

// Uso con React Hook Form
<FormField
  label="Email"
  type="email"
  required
  {...register('email')}
  error={errors.email?.message}
  hint="We'll send the order confirmation here"
/>
```

---

## Validación Avanzada con Zod

```typescript
// Validaciones condicionales
const checkoutSchema = z.object({
  paymentMethod: z.enum(['card', 'transfer', 'cash']),
  cardNumber: z.string().optional(),
  bankAccount: z.string().optional(),
}).superRefine((data, ctx) => {
  if (data.paymentMethod === 'card' && !data.cardNumber) {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: 'Card number is required for card payment',
      path: ['cardNumber'],
    });
  }
  if (data.paymentMethod === 'transfer' && !data.bankAccount) {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: 'Bank account is required for transfer',
      path: ['bankAccount'],
    });
  }
});

// Refinements para validaciones custom
const registrationSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
  confirmPassword: z.string(),
  username: z.string()
    .min(3)
    .max(20)
    .regex(/^[a-z0-9_]+$/, 'Only lowercase letters, numbers, and underscores'),
})
.refine(data => data.password === data.confirmPassword, {
  message: 'Passwords do not match',
  path: ['confirmPassword'],
});

// Transformaciones: normalizar datos al validar
const productSchema = z.object({
  name: z.string().trim().min(1),
  slug: z.string().trim().toLowerCase(),
  price: z.string().transform(val => Math.round(parseFloat(val) * 100)), // €12.50 → 1250 cents
  tags: z.string().transform(val =>
    val.split(',').map(t => t.trim()).filter(Boolean)
  ),
});
```

---

## Multi-Step Form Pattern

```typescript
// Separar schema por paso
const step1Schema = z.object({
  firstName: z.string().min(1),
  lastName: z.string().min(1),
  email: z.string().email(),
});

const step2Schema = z.object({
  address: z.string().min(10),
  city: z.string().min(1),
  country: z.string().min(1),
});

const fullSchema = step1Schema.merge(step2Schema);

type FullFormValues = z.infer<typeof fullSchema>;

function MultiStepForm() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState<Partial<FullFormValues>>({});

  const form = useForm({
    resolver: zodResolver(step === 1 ? step1Schema : step2Schema),
    defaultValues: formData,
  });

  const handleStepSubmit = (data: Partial<FullFormValues>) => {
    const merged = { ...formData, ...data };
    setFormData(merged);

    if (step < 2) {
      setStep(s => s + 1);
      form.reset(merged);  // preservar datos entre pasos
    } else {
      // Submit final con todos los datos
      submitOrder(merged as FullFormValues);
    }
  };

  return (
    <form onSubmit={form.handleSubmit(handleStepSubmit)}>
      <StepIndicator current={step} total={2} />
      {step === 1 && <PersonalInfoStep form={form} />}
      {step === 2 && <ShippingStep form={form} />}
      <div className="flex gap-3 mt-6">
        {step > 1 && (
          <button type="button" onClick={() => setStep(s => s - 1)}>Back</button>
        )}
        <button type="submit">
          {step < 2 ? 'Next' : 'Place Order'}
        </button>
      </div>
    </form>
  );
}
```
