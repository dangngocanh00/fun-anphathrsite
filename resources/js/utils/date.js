export function isoToDmy(iso) {
    if (!iso) return ''
    const [y, m, d] = iso.split('-')
    if (!y || !m || !d) return ''
    return `${d}/${m}/${y}`
}

export function dmyToIso(dmy) {
    if (!dmy) return ''
    const [d, m, y] = dmy.split('/')
    if (!y || !m || !d) return ''
    return `${y}-${m}-${d}`
}
