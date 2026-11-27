import { useLocation } from 'react-router'

interface CameFrom {
  from: string
}

export function useCameFromState(): CameFrom {
  const location = useLocation()

  return { from: location.pathname + location.search }
}

export function useBackToSearch(): string {
  const state = useLocation().state as Partial<CameFrom> | null

  return state?.from ?? '/'
}
