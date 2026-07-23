import { useEffect, useState } from 'react';
import { Mail, MailOpen, Reply, Trash2, X } from 'lucide-react';
import {
  deleteContactMessage,
  fetchContactMessages,
  markMessageRead,
  markMessageReplied,
  type ContactMessage,
} from '../lib/api';

const statusStyles: Record<string, string> = {
  new: 'bg-amber-100 text-amber-700',
  read: 'bg-blue-100 text-blue-700',
  replied: 'bg-emerald-100 text-emerald-700',
};

export function ContactMessages() {
  const [messages, setMessages] = useState<ContactMessage[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchContactMessages()
      .then((res) => setMessages(res.messages.data))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load messages.'))
      .finally(() => setIsLoading(false));
  }

  useEffect(load, []);

  async function handleMarkRead(m: ContactMessage) {
    try {
      await markMessageRead(m.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update message.');
    }
  }

  async function handleMarkReplied(m: ContactMessage) {
    try {
      await markMessageReplied(m.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update message.');
    }
  }

  async function handleDelete(m: ContactMessage) {
    if (!confirm(`Delete the message from ${m.name}?`)) return;
    try {
      await deleteContactMessage(m.id);
      load();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to delete message.');
    }
  }

  return (
    <div className="space-y-6">
      {error && (
        <p className="mirror-card flex items-center justify-between p-4 text-sm text-danger">
          {error}
          <button onClick={() => setError(null)} className="text-danger/60 hover:text-danger">
            <X size={16} />
          </button>
        </p>
      )}

      {isLoading ? (
        <p className="text-sm text-muted">Loading messages…</p>
      ) : messages.length === 0 ? (
        <p className="mirror-card p-6 text-center text-sm text-muted">No messages.</p>
      ) : (
        <div className="mirror-card divide-y divide-ink/5">
          {messages.map((m) => (
            <div key={m.id} className="space-y-2 p-4">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p className="text-sm font-medium text-ink">{m.name}</p>
                  <p className="text-xs text-muted">
                    {m.email}
                    {m.phone ? ` · ${m.phone}` : ''} ·{' '}
                    {new Date(m.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}
                  </p>
                </div>
                <span className={`shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium capitalize ${statusStyles[m.status]}`}>
                  {m.status}
                </span>
              </div>
              <p className="text-sm text-ink/80">{m.message}</p>
              <div className="flex gap-2 pt-1">
                {m.status === 'new' && (
                  <button
                    onClick={() => handleMarkRead(m)}
                    className="flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-ink hover:bg-paper-dim"
                  >
                    <MailOpen size={13} /> Mark Read
                  </button>
                )}
                {m.status !== 'replied' && (
                  <button
                    onClick={() => handleMarkReplied(m)}
                    className="flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-emerald-700 hover:bg-emerald-50"
                  >
                    <Reply size={13} /> Mark Replied
                  </button>
                )}
                <a
                  href={`mailto:${m.email}`}
                  className="flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-ink hover:bg-paper-dim"
                >
                  <Mail size={13} /> Reply by Email
                </a>
                <button
                  onClick={() => handleDelete(m)}
                  className="ml-auto flex items-center gap-1 rounded-lg border border-ink/10 px-2.5 py-1.5 text-xs text-danger hover:bg-danger-bg"
                >
                  <Trash2 size={13} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
