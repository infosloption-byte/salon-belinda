<form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white p-4">
    <div>
        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">From</label>
        <input type="date" name="date_from" value="{{ $from }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
    </div>
    <div>
        <label class="text-xs uppercase tracking-wide block mb-1.5 opacity-60">To</label>
        <input type="date" name="date_to" value="{{ $to }}" class="border px-3 py-2 text-sm" style="border-color: rgba(38,34,32,0.2);">
    </div>
    <button class="btn btn-primary">Update</button>
</form>
