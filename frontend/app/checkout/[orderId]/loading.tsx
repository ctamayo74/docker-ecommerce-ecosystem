export default function Loading() {
  return (
    <div className="mx-auto grid max-w-4xl gap-6 md:grid-cols-2" aria-busy="true">
      <div className="h-64 animate-pulse rounded-lg bg-line" />
      <div className="h-64 animate-pulse rounded-lg bg-line" />
    </div>
  );
}
