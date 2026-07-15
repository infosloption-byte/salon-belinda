export default function Seal({ size = 44 }: { size?: number }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 64 64"
      xmlns="http://www.w3.org/2000/svg"
      role="img"
      aria-label="Salon Belinda monogram"
    >
      <circle cx="32" cy="32" r="31" stroke="var(--color-gold)" strokeWidth="1.2" fill="none" />
      <circle cx="32" cy="32" r="26" stroke="var(--color-gold)" strokeWidth="0.6" fill="none" />
      <text
        x="32"
        y="41"
        textAnchor="middle"
        fontFamily="'Cormorant Garamond', serif"
        fontStyle="italic"
        fontSize="26"
        fill="var(--color-green)"
      >
        SB
      </text>
    </svg>
  );
}
