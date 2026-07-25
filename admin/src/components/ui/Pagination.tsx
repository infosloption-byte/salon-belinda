interface PaginationProps {
  currentPage: number;
  lastPage: number;
  total: number;
  onPageChange: (page: number) => void;
  /** Singular label for the "total" count, e.g. "customer" -> "42 customers". */
  itemLabel?: string;
}

/**
 * Shared prev/next pager for admin list pages. Matches the ad-hoc markup
 * ActivityLog.tsx had before this was pulled out, so existing pages don't
 * visually shift when they adopt it.
 *
 * Renders nothing when there's only one page, so pages can render it
 * unconditionally without an extra `lastPage > 1 &&` guard at the call site.
 */
export function Pagination({ currentPage, lastPage, total, onPageChange, itemLabel }: PaginationProps) {
  if (lastPage <= 1) return null;

  return (
    <div className="flex items-center justify-center gap-3 text-sm">
      <button
        disabled={currentPage <= 1}
        onClick={() => onPageChange(currentPage - 1)}
        className="rounded-lg border border-ink/10 px-3 py-1.5 text-ink disabled:opacity-40"
      >
        Prev
      </button>
      <span className="text-xs text-muted">
        Page {currentPage} of {lastPage} ({total} {itemLabel ? `${itemLabel}${total === 1 ? '' : 's'}` : 'total'})
      </span>
      <button
        disabled={currentPage >= lastPage}
        onClick={() => onPageChange(currentPage + 1)}
        className="rounded-lg border border-ink/10 px-3 py-1.5 text-ink disabled:opacity-40"
      >
        Next
      </button>
    </div>
  );
}
