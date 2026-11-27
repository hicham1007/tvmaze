export type ButtonVariant = 'primary' | 'danger'

const VARIANTS: Record<ButtonVariant, string> = {
  primary: 'bg-white text-black hover:bg-white/80',
  danger: 'bg-accent hover:bg-accent-hover',
}

/** For links that have to look like a button; <Button> uses it too. */
export function buttonClass(variant: ButtonVariant = 'primary') {
  return `inline-block cursor-pointer rounded px-6 py-2.5 font-semibold transition-colors disabled:opacity-60 ${VARIANTS[variant]}`
}
