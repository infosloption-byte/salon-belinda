export default function Seal({ size = 44 }: { size?: number }) {
  return (
    <img
      src="/brand/mark.png"
      alt="Salon Belinda monogram"
      width={size}
      height={size}
      style={{ width: size, height: size, objectFit: 'contain' }}
    />
  );
}
