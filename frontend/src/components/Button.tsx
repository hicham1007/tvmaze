import type { ButtonHTMLAttributes } from 'react'
import { buttonClass, type ButtonVariant } from './buttonClass.ts'

interface Props extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant
}

export function Button({ variant = 'primary', type = 'button', className = '', ...props }: Props) {
  return <button type={type} className={`${buttonClass(variant)} ${className}`} {...props} />
}
