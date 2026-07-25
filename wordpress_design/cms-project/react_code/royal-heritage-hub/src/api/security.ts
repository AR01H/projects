/**
 * Security API — Input validation, XSS protection, rate limiting
 */

export interface ValidationResult {
  valid: boolean;
  errors: string[];
}

// ── Email validation ──
export function validateEmail(email: string): ValidationResult {
  const errors: string[] = [];
  if (!email.trim()) errors.push('Email is required');
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Invalid email format');
  return { valid: errors.length === 0, errors };
}

// ── Phone validation ──
export function validatePhone(phone: string): ValidationResult {
  const errors: string[] = [];
  if (!phone.trim()) errors.push('Phone is required');
  else if (!/^[6-9]\d{9}$/.test(phone.replace(/\s/g, ''))) errors.push('Invalid 10-digit Indian phone number');
  return { valid: errors.length === 0, errors };
}

// ── Password validation ──
export function validatePassword(password: string): ValidationResult {
  const errors: string[] = [];
  if (!password) errors.push('Password is required');
  else {
    if (password.length < 6) errors.push('Password must be at least 6 characters');
    if (password.length > 100) errors.push('Password must be less than 100 characters');
  }
  return { valid: errors.length === 0, errors };
}

// ── Name validation ──
export function validateName(name: string): ValidationResult {
  const errors: string[] = [];
  if (!name.trim()) errors.push('Name is required');
  else if (name.trim().length < 2) errors.push('Name must be at least 2 characters');
  else if (name.trim().length > 100) errors.push('Name must be less than 100 characters');
  return { valid: errors.length === 0, errors };
}

// ── PIN code validation ──
export function validatePincode(pincode: string): ValidationResult {
  const errors: string[] = [];
  if (!pincode.trim()) errors.push('PIN code is required');
  else if (!/^\d{6}$/.test(pincode)) errors.push('PIN code must be 6 digits');
  return { valid: errors.length === 0, errors };
}

// ── Address validation ──
export function validateAddress(address: {
  name: string;
  phone: string;
  email: string;
  line1: string;
  city: string;
  state: string;
  pincode: string;
}): ValidationResult {
  const errors: string[] = [];

  const nameResult = validateName(address.name);
  if (!nameResult.valid) errors.push(...nameResult.errors);

  const phoneResult = validatePhone(address.phone);
  if (!phoneResult.valid) errors.push(...phoneResult.errors);

  const emailResult = validateEmail(address.email);
  if (!emailResult.valid) errors.push(...emailResult.errors);

  if (!address.line1.trim()) errors.push('Address line 1 is required');
  if (!address.city.trim()) errors.push('City is required');
  if (!address.state.trim()) errors.push('State is required');

  const pinResult = validatePincode(address.pincode);
  if (!pinResult.valid) errors.push(...pinResult.errors);

  return { valid: errors.length === 0, errors };
}

// ── XSS sanitization ──
export function sanitizeInput(input: string): string {
  return input
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// ── Strip HTML tags ──
export function stripHtml(html: string): string {
  return html.replace(/<[^>]*>/g, '');
}

// ── Rate limiter (client-side) ──
const rateLimitMap = new Map<string, number[]>();

export function checkRateLimit(key: string, maxAttempts = 5, windowMs = 60000): boolean {
  const now = Date.now();
  const attempts = rateLimitMap.get(key) || [];
  const recentAttempts = attempts.filter((t) => now - t < windowMs);

  if (recentAttempts.length >= maxAttempts) {
    return false; // rate limited
  }

  recentAttempts.push(now);
  rateLimitMap.set(key, recentAttempts);
  return true;
}

// ── CSRF token generation (for forms) ──
export function generateCsrfToken(): string {
  return Array.from(crypto.getRandomValues(new Uint8Array(32)))
    .map((b) => b.toString(16).padStart(2, '0'))
    .join('');
}

// ── Input length limiter ──
export function limitLength(input: string, max: number): string {
  return input.slice(0, max);
}
