import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { CheckCircle2 } from 'lucide-react';
import SectionHeading from '../components/ui/SectionHeading';
import { Button } from '../components/ui/Button';
import { site } from '../data/site';
import { fetchServiceCategories, createAppointment, type ApiServiceCategory } from '../lib/api';

interface BookingForm {
  name: string;
  phone: string;
  email: string;
  serviceId: string; // kept as string for the <select>, converted to number on submit
  date: string;
  time: string;
  notes: string;
}

const emptyForm: BookingForm = {
  name: '',
  phone: '',
  email: '',
  serviceId: '',
  date: '',
  time: '',
  notes: '',
};

export default function Booking() {
  const [params] = useSearchParams();
  const [form, setForm] = useState<BookingForm>(emptyForm);
  const [errors, setErrors] = useState<Partial<Record<keyof BookingForm, string>>>({});
  const [confirmed, setConfirmed] = useState<{ form: BookingForm; serviceName: string; message: string } | null>(
    null
  );
  const [categories, setCategories] = useState<ApiServiceCategory[]>([]);
  const [loadingServices, setLoadingServices] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState('');

  useEffect(() => {
    fetchServiceCategories()
      .then(setCategories)
      .catch(() => setSubmitError('Could not load services right now. Please call us directly or try again shortly.'))
      .finally(() => setLoadingServices(false));
  }, []);

  useEffect(() => {
    const preselect = params.get('service');
    if (preselect) {
      setForm((f) => ({ ...f, serviceId: preselect }));
    }
  }, [params]);

  const update = (key: keyof BookingForm, value: string) => setForm((f) => ({ ...f, [key]: value }));

  const allServices = categories.flatMap((c) => c.services.map((s) => ({ ...s, category: c.title })));

  const validate = () => {
    const next: Partial<Record<keyof BookingForm, string>> = {};
    if (!form.name.trim()) next.name = 'Please enter your name.';
    if (!form.phone.trim()) next.phone = 'Please enter a contact number.';
    if (!form.email.trim()) next.email = 'Please enter your email.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) next.email = 'Please enter a valid email address.';
    if (!form.serviceId) next.serviceId = 'Please select a service.';
    if (!form.date) next.date = 'Please choose a preferred date.';
    if (!form.time) next.time = 'Please choose a preferred time.';
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitError('');
    if (!validate()) return;

    setSubmitting(true);
    try {
      const result = await createAppointment({
        name: form.name,
        phone: form.phone,
        email: form.email,
        service_id: Number(form.serviceId),
        date: form.date,
        time: form.time,
        notes: form.notes || undefined,
      });

      const service = allServices.find((s) => String(s.id) === form.serviceId);
      setConfirmed({ form, serviceName: service?.name ?? 'your selected service', message: result.message });
      setForm(emptyForm);
    } catch (err) {
      setSubmitError(err instanceof Error ? err.message : 'Could not submit your request. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  if (confirmed) {
    return (
      <div className="max-w-xl mx-auto px-5 py-28 text-center">
        <CheckCircle2 size={48} color="var(--color-amber)" className="mx-auto mb-6" />
        <h1 className="font-display text-3xl mb-4" style={{ color: 'var(--color-ink)' }}>
          Request Received
        </h1>
        <p className="text-sm leading-relaxed mb-2" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
          Thank you, {confirmed.form.name}. We've noted your request for{' '}
          <strong>{confirmed.serviceName}</strong> on{' '}
          {new Date(confirmed.form.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long' })} at{' '}
          {confirmed.form.time}.
        </p>
        <p className="text-sm mb-9" style={{ color: 'var(--color-ink)', opacity: 0.7 }}>
          Shanika's team will call you at {confirmed.form.phone} to confirm availability.
        </p>
        <Button onClick={() => setConfirmed(null)} variant="outline">
          Book Another Appointment
        </Button>
      </div>
    );
  }

  return (
    <div>
      <section className="py-20 md:py-24 text-center" style={{ backgroundColor: 'var(--color-deep)' }}>
        <div className="max-w-3xl mx-auto px-5">
          <p className="eyebrow mb-4" style={{ color: 'var(--color-amber-light)' }}>
            Reserve Your Time
          </p>
          <h1 className="font-display text-4xl md:text-5xl" style={{ color: 'var(--color-ivory)' }}>
            Book Appointment
          </h1>
          <p className="mt-5" style={{ color: 'var(--color-ivory)', opacity: 0.75 }}>
            Prefer to speak with us directly? Call {site.phone} and we'll find a time together.
          </p>
        </div>
      </section>

      <div className="max-w-2xl mx-auto px-5 md:px-8 py-20">
        <SectionHeading eyebrow="Appointment Details" title="Tell Us About Your Visit" className="mb-10" />
        <form onSubmit={handleSubmit} className="space-y-5" noValidate>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <Field label="Full Name" error={errors.name}>
              <input
                value={form.name}
                onChange={(e) => update('name', e.target.value)}
                className="input"
                placeholder="Your name"
              />
            </Field>
            <Field label="Phone Number" error={errors.phone}>
              <input
                value={form.phone}
                onChange={(e) => update('phone', e.target.value)}
                className="input"
                placeholder="07X XXX XXXX"
              />
            </Field>
          </div>

          <Field label="Email" error={errors.email}>
            <input
              type="email"
              value={form.email}
              onChange={(e) => update('email', e.target.value)}
              className="input"
              placeholder="you@example.com"
            />
          </Field>

          <Field label="Service" error={errors.serviceId}>
            <select
              value={form.serviceId}
              onChange={(e) => update('serviceId', e.target.value)}
              className="input"
              disabled={loadingServices}
            >
              <option value="">{loadingServices ? 'Loading services…' : 'Select a service'}</option>
              {categories.map((cat) => (
                <optgroup key={cat.id} label={cat.title}>
                  {cat.services.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.name} — {s.price}
                    </option>
                  ))}
                </optgroup>
              ))}
            </select>
          </Field>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <Field label="Preferred Date" error={errors.date}>
              <input
                type="date"
                value={form.date}
                onChange={(e) => update('date', e.target.value)}
                className="input"
              />
            </Field>
            <Field label="Preferred Time" error={errors.time}>
              <input
                type="time"
                value={form.time}
                onChange={(e) => update('time', e.target.value)}
                className="input"
              />
            </Field>
          </div>

          <Field label="Notes (optional)">
            <textarea
              value={form.notes}
              onChange={(e) => update('notes', e.target.value)}
              rows={4}
              className="input resize-none"
              placeholder="Anything we should know — reference photos, allergies, event details..."
            />
          </Field>

          {submitError && (
            <p
              className="text-sm px-4 py-3"
              style={{ backgroundColor: 'var(--color-rose-light, #F3DEDB)', color: 'var(--color-magenta)' }}
            >
              {submitError}
            </p>
          )}

          <Button type="submit" className="w-full mt-2" disabled={submitting}>
            {submitting ? 'Submitting…' : 'Request Appointment'}
          </Button>
        </form>
      </div>

      <style>{`
        .input {
          width: 100%;
          padding: 0.65rem 0.85rem;
          border: 1px solid rgba(38,34,32,0.2);
          background: transparent;
          font-size: 0.9rem;
          color: var(--color-ink);
        }
        .input:focus {
          border-color: var(--color-amber);
          outline: none;
        }
      `}</style>
    </div>
  );
}

function Field({
  label,
  error,
  children,
}: {
  label: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <label className="block">
      <span className="text-xs uppercase tracking-wide block mb-1.5" style={{ color: 'var(--color-ink)', opacity: 0.6 }}>
        {label}
      </span>
      {children}
      {error && (
        <span className="text-xs mt-1 block" style={{ color: 'var(--color-magenta)' }}>
          {error}
        </span>
      )}
    </label>
  );
}