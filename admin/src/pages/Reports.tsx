import { useEffect, useState } from 'react';
import { Download } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { downloadCsv } from '../lib/csv';
import {
  fetchAppointmentsReport,
  fetchBestSellersReport,
  fetchBusiestHoursReport,
  fetchLowStockReport,
  fetchOutstandingBalancesReport,
  fetchRetentionReport,
  fetchRevenueReport,
  fetchStaffCommissionReport,
  type AppointmentsByServiceRow,
  type AppointmentsByStatusRow,
  type BestSellerRow,
  type BusiestHoursRow,
  type OutstandingBalanceJob,
  type Product,
  type RetentionReport,
  type RevenueReport,
  type StaffCommissionReport,
} from '../lib/api';

function formatCurrency(n: number) {
  return `LKR ${Math.round(n).toLocaleString('en-US')}`;
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

type ReportKey =
  | 'revenue'
  | 'best-sellers'
  | 'low-stock'
  | 'appointments'
  | 'outstanding-balances'
  | 'staff-commission'
  | 'busiest-hours'
  | 'retention';

const ADMIN_TABS: { key: ReportKey; label: string; blurb: string }[] = [
  { key: 'revenue', label: 'Revenue', blurb: 'Daily paid revenue and order counts over a date range.' },
  { key: 'best-sellers', label: 'Best Sellers', blurb: 'Top products by units sold and revenue.' },
  { key: 'low-stock', label: 'Low Stock', blurb: 'Products at 10 units or fewer — restock candidates.' },
  { key: 'appointments', label: 'Appointments', blurb: 'Bookings broken down by service and status.' },
  {
    key: 'outstanding-balances',
    label: 'Outstanding Balances',
    blurb: 'Jobs with a balance still due — wedding deposits, unpaid walk-ins, and so on.',
  },
  {
    key: 'staff-commission',
    label: 'Staff Commission',
    blurb: 'Services performed, revenue generated, and commission earned per staff member.',
  },
  {
    key: 'busiest-hours',
    label: 'Busiest Hours',
    blurb: 'Bookings by hour of day — useful for staffing decisions.',
  },
  {
    key: 'retention',
    label: 'Retention',
    blurb: 'New vs. returning customers, and the repeat-customer rate over a date range.',
  },
];

function defaultDates(daysBack: number) {
  const to = new Date().toISOString().slice(0, 10);
  const from = new Date(Date.now() - daysBack * 86400000).toISOString().slice(0, 10);
  return { from, to };
}

function DateRangeForm({
  from,
  to,
  onFrom,
  onTo,
  onSubmit,
}: {
  from: string;
  to: string;
  onFrom: (v: string) => void;
  onTo: (v: string) => void;
  onSubmit: () => void;
}) {
  return (
    <div className="mirror-card flex flex-wrap items-end gap-3 p-4">
      <label>
        <span className="mb-1 block text-xs text-muted">From</span>
        <input
          type="date"
          value={from}
          onChange={(e) => onFrom(e.target.value)}
          className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
        />
      </label>
      <label>
        <span className="mb-1 block text-xs text-muted">To</span>
        <input
          type="date"
          value={to}
          onChange={(e) => onTo(e.target.value)}
          className="rounded-lg border border-ink/10 bg-paper px-3 py-2 text-sm outline-none focus:border-gold"
        />
      </label>
      <button onClick={onSubmit} className="rounded-lg bg-wine px-4 py-2 text-sm font-medium text-paper hover:bg-wine-light">
        Update
      </button>
    </div>
  );
}

function RevenuePanel() {
  const initial = defaultDates(29);
  const [from, setFrom] = useState(initial.from);
  const [to, setTo] = useState(initial.to);
  const [report, setReport] = useState<RevenueReport | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchRevenueReport({ date_from: from, date_to: to })
      .then(setReport)
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load revenue report.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, []);

  function exportCsv() {
    if (!report) return;
    downloadCsv(
      `revenue-${from}-to-${to}`,
      report.combined.map((row) => ({
        date: row.day,
        shop_orders: row.orders_count,
        shop_revenue: row.shop,
        salon_payments: row.payments_count,
        salon_revenue: row.salon,
        total: row.total,
      })),
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <DateRangeForm from={from} to={to} onFrom={setFrom} onTo={setTo} onSubmit={load} />
        <ExportCsvButton onExport={exportCsv} disabled={!report || report.combined.length === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : report ? (
        <>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div className="mirror-card p-5">
              <p className="text-xs uppercase tracking-wide text-muted">Total Revenue</p>
              <p className="mt-2 font-display text-2xl text-wine">{formatCurrency(report.totalRevenue)}</p>
            </div>
            <div className="mirror-card p-5">
              <p className="text-xs uppercase tracking-wide text-muted">Shop ({report.totalOrders} orders)</p>
              <p className="mt-2 font-display text-2xl text-ink">{formatCurrency(report.totalShopRevenue)}</p>
            </div>
            <div className="mirror-card p-5">
              <p className="text-xs uppercase tracking-wide text-muted">Salon (Jobs)</p>
              <p className="mt-2 font-display text-2xl text-ink">{formatCurrency(report.totalSalonRevenue)}</p>
            </div>
            <div className="mirror-card p-5">
              <p className="text-xs uppercase tracking-wide text-muted">vs. Previous Period</p>
              {report.growthPercent === null ? (
                <p className="mt-2 font-display text-2xl text-ink">—</p>
              ) : (
                <p className={`mt-2 font-display text-2xl ${report.growthPercent >= 0 ? 'text-emerald-600' : 'text-danger'}`}>
                  {report.growthPercent >= 0 ? '+' : ''}
                  {report.growthPercent}%
                </p>
              )}
              <p className="mt-1 text-xs text-muted">
                {formatCurrency(report.previousTotalRevenue)} ({formatDate(report.previousFrom)}–{formatDate(report.previousTo)})
              </p>
            </div>
          </div>

          <div className="mirror-card overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-ink/10 text-left">
                  <th className="p-4">Date</th>
                  <th className="p-4">Shop Orders</th>
                  <th className="p-4">Shop Revenue</th>
                  <th className="p-4">Salon Payments</th>
                  <th className="p-4">Salon Revenue</th>
                  <th className="p-4">Total</th>
                </tr>
              </thead>
              <tbody>
                {report.combined.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="p-8 text-center text-sm text-muted">
                      No revenue in this range.
                    </td>
                  </tr>
                ) : (
                  report.combined.map((row) => (
                    <tr key={row.day} className="border-b border-ink/5">
                      <td className="p-4">{formatDate(row.day)}</td>
                      <td className="p-4">{row.orders_count}</td>
                      <td className="p-4">{formatCurrency(row.shop)}</td>
                      <td className="p-4">{row.payments_count}</td>
                      <td className="p-4">{formatCurrency(row.salon)}</td>
                      <td className="p-4 font-medium text-ink">{formatCurrency(row.total)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </>
      ) : null}
    </div>
  );
}

function ExportCsvButton({ onExport, disabled }: { onExport: () => void; disabled?: boolean }) {
  return (
    <button
      onClick={onExport}
      disabled={disabled}
      className="inline-flex items-center gap-1.5 rounded-lg border border-ink/10 px-3 py-2 text-sm text-ink hover:bg-paper-dim disabled:cursor-not-allowed disabled:opacity-40"
    >
      <Download size={14} />
      Export CSV
    </button>
  );
}

function BestSellersPanel() {
  const initial = defaultDates(89);
  const [from, setFrom] = useState(initial.from);
  const [to, setTo] = useState(initial.to);
  const [rows, setRows] = useState<BestSellerRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchBestSellersReport({ date_from: from, date_to: to })
      .then((res) => setRows(res.bestSellers))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load best sellers.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, []);

  function exportCsv() {
    downloadCsv(
      `best-sellers-${from}-to-${to}`,
      rows.map((row) => ({ product: row.product_name, units_sold: row.units_sold, revenue: row.revenue })),
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <DateRangeForm from={from} to={to} onFrom={setFrom} onTo={setTo} onSubmit={load} />
        <ExportCsvButton onExport={exportCsv} disabled={rows.length === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : (
        <div className="mirror-card overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-ink/10 text-left">
                <th className="p-4">#</th>
                <th className="p-4">Product</th>
                <th className="p-4">Units Sold</th>
                <th className="p-4">Revenue</th>
              </tr>
            </thead>
            <tbody>
              {rows.length === 0 ? (
                <tr>
                  <td colSpan={4} className="p-8 text-center text-sm text-muted">
                    No sales in this range.
                  </td>
                </tr>
              ) : (
                rows.map((row, i) => (
                  <tr key={`${row.product_id ?? row.product_name}-${i}`} className="border-b border-ink/5">
                    <td className="p-4">{i + 1}</td>
                    <td className="p-4">{row.product_name}</td>
                    <td className="p-4">{row.units_sold}</td>
                    <td className="p-4">{formatCurrency(row.revenue)}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

function LowStockPanel() {
  const [products, setProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchLowStockReport()
      .then((res) => setProducts(res.products))
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load low stock report.'))
      .finally(() => setIsLoading(false));
  }, []);

  function exportCsv() {
    downloadCsv(
      'low-stock',
      products.map((p) => ({ product: p.name, category: p.category, stock: p.stock_count, reorder_point: p.reorder_point ?? 10 })),
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex justify-end">
        <ExportCsvButton onExport={exportCsv} disabled={products.length === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : (
        <div className="mirror-card overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-ink/10 text-left">
                <th className="p-4">Product</th>
                <th className="p-4">Category</th>
                <th className="p-4">Stock</th>
                <th className="p-4">Reorder Point</th>
              </tr>
            </thead>
            <tbody>
              {products.length === 0 ? (
                <tr>
                  <td colSpan={4} className="p-8 text-center text-sm text-muted">
                    Nothing low on stock right now.
                  </td>
                </tr>
              ) : (
                products.map((p) => (
                  <tr key={p.id} className="border-b border-ink/5">
                    <td className="p-4">{p.name}</td>
                    <td className="p-4">{p.category}</td>
                    <td className="p-4">
                      <span
                        className={`rounded-full px-2 py-1 text-xs font-medium ${
                          p.stock_count === 0 ? 'bg-danger-bg text-danger' : 'bg-gold-light/40 text-ink'
                        }`}
                      >
                        {p.stock_count}
                      </span>
                    </td>
                    <td className="p-4 text-muted">{p.reorder_point ?? 10}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

function AppointmentsReportPanel() {
  const initial = defaultDates(29);
  const [from, setFrom] = useState(initial.from);
  const [to, setTo] = useState(initial.to);
  const [byService, setByService] = useState<AppointmentsByServiceRow[]>([]);
  const [byStatus, setByStatus] = useState<AppointmentsByStatusRow[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchAppointmentsReport({ date_from: from, date_to: to })
      .then((res) => {
        setByService(res.byService);
        setByStatus(res.byStatus);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load appointments report.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, []);

  function exportCsv() {
    downloadCsv(`appointments-${from}-to-${to}`, [
      ...byService.map((row) => ({ breakdown: 'by_service', label: row.service_name, count: row.total })),
      ...byStatus.map((row) => ({ breakdown: 'by_status', label: row.status, count: row.total })),
    ]);
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <DateRangeForm from={from} to={to} onFrom={setFrom} onTo={setTo} onSubmit={load} />
        <ExportCsvButton onExport={exportCsv} disabled={byService.length === 0 && byStatus.length === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : (
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div className="mirror-card overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-ink/10 text-left">
                  <th className="p-4">Service</th>
                  <th className="p-4">Bookings</th>
                </tr>
              </thead>
              <tbody>
                {byService.length === 0 ? (
                  <tr>
                    <td colSpan={2} className="p-8 text-center text-sm text-muted">
                      No bookings in this range.
                    </td>
                  </tr>
                ) : (
                  byService.map((row) => (
                    <tr key={row.service_name} className="border-b border-ink/5">
                      <td className="p-4">{row.service_name}</td>
                      <td className="p-4">{row.total}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
          <div className="mirror-card overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-ink/10 text-left">
                  <th className="p-4">Status</th>
                  <th className="p-4">Count</th>
                </tr>
              </thead>
              <tbody>
                {byStatus.length === 0 ? (
                  <tr>
                    <td colSpan={2} className="p-8 text-center text-sm text-muted">
                      No bookings in this range.
                    </td>
                  </tr>
                ) : (
                  byStatus.map((row) => (
                    <tr key={row.status} className="border-b border-ink/5">
                      <td className="p-4 capitalize">{row.status}</td>
                      <td className="p-4">{row.total}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}

function OutstandingBalancesPanel() {
  const [jobs, setJobs] = useState<OutstandingBalanceJob[]>([]);
  const [total, setTotal] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchOutstandingBalancesReport()
      .then((res) => {
        setJobs(res.jobs);
        setTotal(res.totalOutstanding);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load outstanding balances.'))
      .finally(() => setIsLoading(false));
  }, []);

  function exportCsv() {
    downloadCsv(
      'outstanding-balances',
      jobs.map((job) => ({
        job_date: job.job_date,
        customer: job.customer?.name ?? '',
        phone: job.customer?.phone ?? '',
        status: job.status,
        total: job.subtotal,
        paid: job.total_paid,
        balance_due: job.balance_due,
      })),
    );
  }

  return (
    <div className="space-y-4">
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div className="mirror-card max-w-xs p-5">
          <p className="text-xs uppercase tracking-wide text-muted">Total Outstanding</p>
          <p className="mt-2 font-display text-2xl text-wine">{formatCurrency(total)}</p>
        </div>
        <ExportCsvButton onExport={exportCsv} disabled={jobs.length === 0} />
      </div>
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : (
        <div className="mirror-card overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-ink/10 text-left">
                <th className="p-4">Job Date</th>
                <th className="p-4">Customer</th>
                <th className="p-4">Status</th>
                <th className="p-4">Total</th>
                <th className="p-4">Paid</th>
                <th className="p-4">Balance Due</th>
              </tr>
            </thead>
            <tbody>
              {jobs.length === 0 ? (
                <tr>
                  <td colSpan={6} className="p-8 text-center text-sm text-muted">
                    Nothing outstanding — all jobs are settled.
                  </td>
                </tr>
              ) : (
                jobs.map((job) => (
                  <tr key={job.id} className="border-b border-ink/5">
                    <td className="p-4">{formatDate(job.job_date)}</td>
                    <td className="p-4">
                      {job.customer?.name} · {job.customer?.phone}
                    </td>
                    <td className="p-4">
                      <span className="rounded-full bg-gold-light/40 px-2 py-1 text-xs font-medium capitalize text-ink">
                        {job.status.replace('_', ' ')}
                      </span>
                    </td>
                    <td className="p-4">{formatCurrency(job.subtotal)}</td>
                    <td className="p-4">{formatCurrency(job.total_paid)}</td>
                    <td className="p-4 font-medium text-wine">{formatCurrency(job.balance_due)}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

function StaffCommissionPanel({ isAdminRole }: { isAdminRole: boolean }) {
  const initial = defaultDates(29);
  const [from, setFrom] = useState(initial.from);
  const [to, setTo] = useState(initial.to);
  const [staffId, setStaffId] = useState<number | ''>('');
  const [report, setReport] = useState<StaffCommissionReport | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load(overrideStaffId?: number | '') {
    setIsLoading(true);
    const sid = overrideStaffId !== undefined ? overrideStaffId : staffId;
    fetchStaffCommissionReport({ date_from: from, date_to: to, staff_id: sid || undefined })
      .then(setReport)
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load staff commission report.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, []);

  const own = report?.summary?.[0];

  function exportCsv() {
    if (!report) return;
    if (report.staffId && report.detail) {
      downloadCsv(
        `staff-commission-detail-${from}-to-${to}`,
        report.detail.map((item) => ({
          job_date: item.job?.job_date ?? '',
          customer: item.job?.customer?.name ?? '',
          treatment: item.service_name,
          price: item.final_price,
          commission: item.commission_amount,
        })),
      );
    } else {
      downloadCsv(
        `staff-commission-summary-${from}-to-${to}`,
        report.summary.map((row) => ({
          staff: row.name,
          role: row.role_title,
          services: row.services_count,
          revenue: row.revenue,
          commission: row.commission,
        })),
      );
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <DateRangeForm from={from} to={to} onFrom={setFrom} onTo={setTo} onSubmit={() => load()} />
        <ExportCsvButton onExport={exportCsv} disabled={!report || report.summary.length === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : report ? (
        <>
          {isAdminRole ? (
            <div className="mirror-card overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-ink/10 text-left">
                    <th className="p-4">Staff</th>
                    <th className="p-4">Role</th>
                    <th className="p-4">Services</th>
                    <th className="p-4">Revenue</th>
                    <th className="p-4">Commission</th>
                    <th className="p-4" />
                  </tr>
                </thead>
                <tbody>
                  {report.summary.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="p-8 text-center text-sm text-muted">
                        No services performed in this range.
                      </td>
                    </tr>
                  ) : (
                    report.summary.map((row) => (
                      <tr
                        key={row.staff_id}
                        className={`border-b border-ink/5 ${
                          report.staffId === row.staff_id ? 'bg-gold-light/20' : ''
                        }`}
                      >
                        <td className="p-4">{row.name}</td>
                        <td className="p-4 text-muted">{row.role_title}</td>
                        <td className="p-4">{row.services_count}</td>
                        <td className="p-4">{formatCurrency(row.revenue)}</td>
                        <td className="p-4 font-medium text-wine">{formatCurrency(row.commission)}</td>
                        <td className="p-4">
                          <button
                            onClick={() => {
                              setStaffId(row.staff_id);
                              load(row.staff_id);
                            }}
                            className="text-xs underline text-ink hover:text-wine"
                          >
                            View Detail
                          </button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div className="mirror-card p-5">
                <p className="text-xs uppercase tracking-wide text-muted">Services Performed</p>
                <p className="mt-2 font-display text-2xl text-ink">{own?.services_count ?? 0}</p>
              </div>
              <div className="mirror-card p-5">
                <p className="text-xs uppercase tracking-wide text-muted">Revenue Generated</p>
                <p className="mt-2 font-display text-2xl text-ink">{formatCurrency(own?.revenue ?? 0)}</p>
              </div>
              <div className="mirror-card p-5">
                <p className="text-xs uppercase tracking-wide text-muted">Commission Earned</p>
                <p className="mt-2 font-display text-2xl text-wine">{formatCurrency(own?.commission ?? 0)}</p>
              </div>
            </div>
          )}

          {report.staffId && (
            <>
              {isAdminRole && (
                <button
                  onClick={() => {
                    setStaffId('');
                    load('');
                  }}
                  className="text-sm text-ink underline hover:text-wine"
                >
                  &larr; Back to all staff
                </button>
              )}
              <h3 className="font-display text-lg text-ink">{isAdminRole ? 'Service Breakdown' : 'My Services'}</h3>
              <div className="mirror-card overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-ink/10 text-left">
                      <th className="p-4">Job Date</th>
                      <th className="p-4">Customer</th>
                      <th className="p-4">Treatment</th>
                      <th className="p-4">Price</th>
                      <th className="p-4">Commission</th>
                    </tr>
                  </thead>
                  <tbody>
                    {!report.detail || report.detail.length === 0 ? (
                      <tr>
                        <td colSpan={5} className="p-8 text-center text-sm text-muted">
                          No services in this range.
                        </td>
                      </tr>
                    ) : (
                      report.detail.map((item) => (
                        <tr key={item.id} className="border-b border-ink/5">
                          <td className="p-4">{item.job?.job_date ? formatDate(item.job.job_date) : '—'}</td>
                          <td className="p-4">{item.job?.customer?.name ?? '—'}</td>
                          <td className="p-4">{item.service_name}</td>
                          <td className="p-4">{formatCurrency(item.final_price)}</td>
                          <td className="p-4">{formatCurrency(item.commission_amount)}</td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </>
          )}
        </>
      ) : null}
    </div>
  );
}

function BusiestHoursPanel() {
  const initial = defaultDates(29);
  const [from, setFrom] = useState(initial.from);
  const [to, setTo] = useState(initial.to);
  const [hours, setHours] = useState<BusiestHoursRow[]>([]);
  const [excludedCount, setExcludedCount] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchBusiestHoursReport({ date_from: from, date_to: to })
      .then((res) => {
        setHours(res.hours);
        setExcludedCount(res.excludedCount);
      })
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load busiest-hours report.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, []);

  const maxCount = Math.max(1, ...hours.map((h) => h.count));
  const busiest = hours.length ? hours.reduce((a, b) => (b.count > a.count ? b : a)) : null;

  function exportCsv() {
    downloadCsv(
      `busiest-hours-${from}-to-${to}`,
      hours.map((row) => ({ hour: row.label, bookings: row.count })),
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <DateRangeForm from={from} to={to} onFrom={setFrom} onTo={setTo} onSubmit={load} />
        <ExportCsvButton onExport={exportCsv} disabled={hours.length === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : (
        <>
          {busiest && busiest.count > 0 && (
            <div className="mirror-card max-w-xs p-5">
              <p className="text-xs uppercase tracking-wide text-muted">Busiest Hour</p>
              <p className="mt-2 font-display text-2xl text-wine">{busiest.label}</p>
              <p className="mt-1 text-xs text-muted">{busiest.count} bookings</p>
            </div>
          )}
          <div className="mirror-card space-y-2 p-4">
            {hours.every((h) => h.count === 0) ? (
              <p className="p-4 text-center text-sm text-muted">No bookings with a parseable time in this range.</p>
            ) : (
              hours.map((row) => (
                <div key={row.hour} className="flex items-center gap-3">
                  <span className="w-14 shrink-0 text-xs text-muted">{row.label}</span>
                  <div className="h-4 flex-1 overflow-hidden rounded-full bg-paper-dim">
                    <div
                      className="h-full rounded-full bg-wine"
                      style={{ width: `${(row.count / maxCount) * 100}%` }}
                    />
                  </div>
                  <span className="w-8 shrink-0 text-right text-xs text-ink">{row.count}</span>
                </div>
              ))
            )}
          </div>
          {excludedCount > 0 && (
            <p className="text-xs text-muted">
              {excludedCount} booking{excludedCount === 1 ? '' : 's'} excluded — time couldn't be parsed.
            </p>
          )}
        </>
      )}
    </div>
  );
}

function RetentionPanel() {
  const initial = defaultDates(29);
  const [from, setFrom] = useState(initial.from);
  const [to, setTo] = useState(initial.to);
  const [report, setReport] = useState<RetentionReport | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  function load() {
    setIsLoading(true);
    fetchRetentionReport({ date_from: from, date_to: to })
      .then(setReport)
      .catch((err) => setError(err instanceof Error ? err.message : 'Failed to load retention report.'))
      .finally(() => setIsLoading(false));
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(load, []);

  function exportCsv() {
    if (!report) return;
    downloadCsv(`retention-${from}-to-${to}`, [
      {
        total_customers: report.totalCustomers,
        returning_customers: report.returningCustomers,
        new_customers: report.newCustomers,
        retention_rate_percent: report.retentionRate,
      },
    ]);
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <DateRangeForm from={from} to={to} onFrom={setFrom} onTo={setTo} onSubmit={load} />
        <ExportCsvButton onExport={exportCsv} disabled={!report || report.totalCustomers === 0} />
      </div>
      {error && <p className="mirror-card p-4 text-sm text-danger">{error}</p>}
      {isLoading ? (
        <p className="text-sm text-muted">Loading…</p>
      ) : report ? (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div className="mirror-card p-5">
            <p className="text-xs uppercase tracking-wide text-muted">Retention Rate</p>
            <p className="mt-2 font-display text-2xl text-wine">{report.retentionRate}%</p>
          </div>
          <div className="mirror-card p-5">
            <p className="text-xs uppercase tracking-wide text-muted">Customers Seen</p>
            <p className="mt-2 font-display text-2xl text-ink">{report.totalCustomers}</p>
          </div>
          <div className="mirror-card p-5">
            <p className="text-xs uppercase tracking-wide text-muted">Returning</p>
            <p className="mt-2 font-display text-2xl text-ink">{report.returningCustomers}</p>
          </div>
          <div className="mirror-card p-5">
            <p className="text-xs uppercase tracking-wide text-muted">New</p>
            <p className="mt-2 font-display text-2xl text-ink">{report.newCustomers}</p>
          </div>
        </div>
      ) : null}
    </div>
  );
}

export function Reports() {
  const { user } = useAuth();
  const isAdminRole = user?.role === 'admin';
  const [active, setActive] = useState<ReportKey | null>(isAdminRole ? null : 'staff-commission');

  if (!isAdminRole) {
    return (
      <div className="space-y-4">
        <h1 className="font-display text-xl text-ink">My Commission</h1>
        <StaffCommissionPanel isAdminRole={false} />
      </div>
    );
  }

  if (!active) {
    return (
      <div className="space-y-6">
        <h1 className="font-display text-xl text-ink">Reports</h1>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          {ADMIN_TABS.map((tab) => (
            <button
              key={tab.key}
              onClick={() => setActive(tab.key)}
              className="mirror-card p-6 text-left transition-shadow hover:shadow-md"
            >
              <p className="font-display text-lg text-ink">{tab.label}</p>
              <p className="mt-2 text-sm text-muted">{tab.blurb}</p>
            </button>
          ))}
        </div>
      </div>
    );
  }

  const activeTab = ADMIN_TABS.find((t) => t.key === active);

  return (
    <div className="space-y-4">
      <button onClick={() => setActive(null)} className="text-sm text-ink underline hover:text-wine">
        &larr; All Reports
      </button>
      <h1 className="font-display text-xl text-ink">{activeTab?.label}</h1>

      {active === 'revenue' && <RevenuePanel />}
      {active === 'best-sellers' && <BestSellersPanel />}
      {active === 'low-stock' && <LowStockPanel />}
      {active === 'appointments' && <AppointmentsReportPanel />}
      {active === 'outstanding-balances' && <OutstandingBalancesPanel />}
      {active === 'staff-commission' && <StaffCommissionPanel isAdminRole />}
      {active === 'busiest-hours' && <BusiestHoursPanel />}
      {active === 'retention' && <RetentionPanel />}
    </div>
  );
}
