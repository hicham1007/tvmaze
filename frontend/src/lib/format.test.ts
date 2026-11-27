import { describe, expect, it } from 'vitest'
import { formatDate, formatHostname, formatSchedule, formatYear, formatYears } from './format.ts'

describe('formatDate', () => {
  it('writes the date out in full', () => {
    expect(formatDate('2011-04-17')).toBe('April 17, 2011')
  })

  it('keeps the calendar day whatever the time zone of the reader', () => {
    expect(formatDate('2020-01-01')).toBe('January 1, 2020')
    expect(formatDate('2019-12-31')).toBe('December 31, 2019')
  })

  it('is null when there is no date', () => {
    expect(formatDate(null)).toBeNull()
  })
})

describe('formatYear and formatYears', () => {
  it('takes the year of a date', () => {
    expect(formatYear('2008-01-20')).toBe('2008')
    expect(formatYear(null)).toBeNull()
  })

  it('joins the first and last year of a show', () => {
    expect(formatYears('2011-04-17', '2019-05-19')).toBe('2011 – 2019')
  })

  it('shows one year when the other is unknown, and nothing without both', () => {
    expect(formatYears('2011-04-17', null)).toBe('2011')
    expect(formatYears(null, '2019-05-19')).toBe('2019')
    expect(formatYears(null, null)).toBeNull()
  })
})

describe('formatSchedule', () => {
  it('lists the days and appends the time', () => {
    expect(formatSchedule({ days: ['Sunday'], time: '21:00' })).toBe('Sunday · 21:00')
    expect(formatSchedule({ days: ['Monday', 'Wednesday'], time: '20:30' })).toBe('Monday, Wednesday · 20:30')
  })

  it('leaves the time out when it is unknown', () => {
    expect(formatSchedule({ days: ['Friday'], time: null })).toBe('Friday')
  })

  it('is null without days, even if there is a time', () => {
    expect(formatSchedule({ days: [], time: null })).toBeNull()
    expect(formatSchedule({ days: [], time: '21:00' })).toBeNull()
  })
})

describe('formatHostname', () => {
  it('keeps the host without www', () => {
    expect(formatHostname('http://www.hbo.com/game-of-thrones')).toBe('hbo.com')
    expect(formatHostname('https://netflix.com/title/80100172')).toBe('netflix.com')
  })

  it('is null for a missing or malformed address', () => {
    expect(formatHostname(null)).toBeNull()
    expect(formatHostname('not a url')).toBeNull()
  })

  it('is null for anything that is not a web address, so it never becomes a link', () => {
    expect(formatHostname('javascript:alert(1)')).toBeNull()
    expect(formatHostname('data:text/html,<p>hi</p>')).toBeNull()
    expect(formatHostname('ftp://example.com')).toBeNull()
  })
})
