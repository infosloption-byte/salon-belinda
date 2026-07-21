import { site } from '../../data/site';

export default function Seal({ size = 44 }: { size?: number }) {
  return (
    <img
      src="/brand/mark.png"
      alt={`${site.name} monogram`}
      width={size}
      height={size}
      style={{ width: size, height: size, objectFit: 'contain' }}
    />
  );
}
