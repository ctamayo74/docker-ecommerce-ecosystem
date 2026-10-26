export default function Loading() {
  return (
    <div className="mt-8 grid gap-8 md:grid-cols-2" aria-busy="true">
      <div className="aspect-square animate-pulse rounded-lg bg-line" />
      <div className="space-y-4">
        <div className="h-9 w-3/4 animate-pulse rounded bg-line" />
        <div className="h-9 w-1/3 animate-pulse rounded bg-line" />
        <div className="h-24 animate-pulse rounded bg-line" />
      </div>
    </div>
  );
}
