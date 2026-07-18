// Base cost/output for the 15 upgrade tiers. Display names come from the
// `game.upgradeNames` i18n array (same index) so they stay translatable.
export const UPGRADE_BASE = [
  { cps: 1, price: 10 },
  { cps: 3, price: 100 },
  { cps: 8, price: 750 },
  { cps: 15, price: 3000 },
  { cps: 40, price: 15000 },
  { cps: 120, price: 80000 },
  { cps: 350, price: 500000 },
  { cps: 1200, price: 3000000 },
  { cps: 5000, price: 20000000 },
  { cps: 25000, price: 150000000 },
  { cps: 100000, price: 1_000_000_000 },
  { cps: 500000, price: 10_000_000_000 },
  { cps: 2000000, price: 100_000_000_000 },
  { cps: 10000000, price: 1_000_000_000_000 },
  { cps: 100000000, price: 10_000_000_000_000 },
]

export const UPGRADE_MULTIPLIER = 1.2
export const REBIRTH_BASE_PRICE = 15_000_000_000_000
