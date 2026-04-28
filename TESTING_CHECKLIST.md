# Testing Checklist — Opción C Implementation

## ✅ Pre-Testing Setup

- [ ] Git status clean (no uncommitted changes)
- [ ] Run migrations (if any new models): `php artisan migrate`
- [ ] Clear application cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Restart dev server: `php artisan serve` (or similar)
- [ ] Test in Chrome/Safari/Firefox (desktop and mobile)

---

## 📱 1. HOME PAGE (`/`)

### Hero Section & Intention Selector
- [ ] Hero section displays "Búsqueda asistida de inmuebles en Benito Juárez" title
- [ ] Subtitle: "Propiedades verificadas con asesoría personalizada de expertos. Sin perder tiempo en visitas innecesarias."
- [ ] 3 intention cards visible:
  - [ ] "Comprar" → Links to `/comprar`
  - [ ] "Vender" → Links to `/vende-tu-propiedad`
  - [ ] "Desarrolladores e Inversionistas" → Links to `/desarrolladores-e-inversionistas`
- [ ] Animations work (fade-in-up on scroll)
- [ ] Responsive on mobile (cards stack vertically)

---

## 🛒 2. COMPRAR PAGE (`/comprar`)

### Hero Section
- [ ] Title: "Encuentra tu próximo hogar en Benito Juárez"
- [ ] Subtitle displays correctly
- [ ] 3 trust signals visible (pre-calificadas, gratuita, seguridad)
- [ ] BuyerSearchForm component renders in right column
- [ ] Form fields are interactive

### Ventajas Section
- [ ] 3-card grid displays:
  - [ ] "Búsqueda rápida" with lightning icon
  - [ ] "100% verificadas" with shield-check icon
  - [ ] "Asesoría experta" with user-check icon
- [ ] Cards have hover effect (border + shadow change)
- [ ] Text is legible and consistent

### Cómo Funciona Section
- [ ] 3-step process displays with numbered badges (01, 02, 03)
- [ ] Steps: "Cuéntanos qué buscas" → "Asesoría personalizada" → "Cierra tu hogar"
- [ ] Cards have animation-delay for staggered entrance
- [ ] Hover effects work

### FAQ Section (Accordion)
- [ ] 4 collapsible details elements display:
  - [ ] "¿Cuál es el proceso de búsqueda?"
  - [ ] "¿Hay costo por la asesoría?"
  - [ ] "¿Qué sucede después de enviar el formulario?"
  - [ ] "¿Están todas las propiedades verificadas?"
- [ ] Clicking question expands answer
- [ ] Chevron icon rotates 180° on open
- [ ] Only one FAQ can be open at a time (if using native `<details>`)
- [ ] Text content is accurate (no orphaned text)

### Responsive Design
- [ ] Desktop (1280px+): All sections display in proper grid
- [ ] Tablet (768px): Cards adjust spacing
- [ ] Mobile (375px): Cards stack, text is readable

---

## 🏗️ 3. DESARROLLADORES PAGE (`/desarrolladores-e-inversionistas`)

### Hero Section
- [ ] Title: "Terrenos e inversión inmobiliaria en Benito Juárez"
- [ ] Badge: "Captación B2B"
- [ ] 3 trust signals visible
- [ ] DeveloperBriefForm component renders on right

### Líneas de Captación Section
- [ ] 4-card grid displays:
  - [ ] "Terrenos" with home icon
  - [ ] "Producto Terminado" with building icon
  - [ ] "Coinversión" with handshake icon
  - [ ] "Asesoría" with chart-bar icon
- [ ] All icons render correctly

### Ventajas Section
- [ ] 3-card grid displays:
  - [ ] "Demanda Verificada" with target icon
  - [ ] "Expertise Integral" with briefcase icon
  - [ ] "Red Consolidada" with users icon
- [ ] Cards have hover effects
- [ ] Descriptions are accurate

### Cómo Funciona Section
- [ ] 3-step process with numbered badges
- [ ] Steps: "Presenta tu activo" → "Análisis y valuación" → "Intermediación y cierre"
- [ ] Animations and hover effects work

### FAQ Section
- [ ] 5 collapsible questions display:
  - [ ] "¿Cuánto cuesta usar los servicios...?"
  - [ ] "¿Qué tan rápido pueden colocar un proyecto?"
  - [ ] "¿Cuáles son los requisitos para presentar...?"
  - [ ] "¿Cómo funciona la intermediación...?"
  - [ ] "¿Ofrecen asesoría en estructuración...?"
- [ ] All questions expand/collapse correctly
- [ ] Content is accurate and relevant

### Responsive Design
- [ ] All sections adjust properly on smaller screens

---

## 4. HEADER & FOOTER

### Header (navbar.blade.php)
- [ ] Logo displays (HV gradient circle or image)
- [ ] **Desktop (lg:)**: Slogan "Pocos inmuebles. Más control." appears below logo in light gray (text-xs)
- [ ] **Mobile**: Slogan hidden (hidden lg:block working)
- [ ] Navigation links display correctly (Propiedades, Precios de Mercado, etc.)
- [ ] "Vende tu propiedad" CTA button visible with gradient
- [ ] Mobile hamburger menu works
- [ ] Sticky header effect (scrolled state has glass + shadow)

### Footer (footer.blade.php)
- [ ] Logo with slogan "Pocos inmuebles. Más control. Mejores resultados." displays
- [ ] 4-column layout:
  - [ ] Brand column (with social links)
  - [ ] "Explorar" links
  - [ ] "Servicios" links
  - [ ] "Contacto" section
- [ ] Social icons (Facebook, Instagram, TikTok, WhatsApp) link correctly
- [ ] Trust badges display (SSL Seguro, AMPI)
- [ ] Copyright year is current
- [ ] Legal links work (/legal/aviso-de-privacidad, /terminos-y-condiciones, /politica-de-cookies)

---

## 🎨 5. DESIGN & STYLING

### Tailwind Theme Colors
- [ ] Brand colors apply: --color-brand-500 (#3B82C4), brand-950 (#0C1A2E)
- [ ] Navy colors apply: --color-navy-950, --color-navy-900
- [ ] Semantic colors: text, muted, border, surface, success, error, warning
- [ ] Gradients render: `gradient-brand` on buttons and badges

### Typography
- [ ] All text is readable (sufficient contrast)
- [ ] Spanish text displays correctly (tildes, accents)
- [ ] Font weights apply: bold (600), semibold (500), regular (400)
- [ ] Line heights are comfortable (1.5-1.6)

### Animations
- [ ] `animate-fade-in-up` works on scroll
- [ ] `animate-slide-in-right` works on page load
- [ ] `animate-scale-in` works on modal/form appearance
- [ ] `animate-float` works on background elements
- [ ] No layout shifts during animations

### Shadows & Effects
- [ ] `.shadow-premium`, `.shadow-premium-lg`, `.shadow-premium-xl` display
- [ ] `.glass` effect on navbar (blurred, semi-transparent)
- [ ] Hover states on cards have shadow increase

---

## 📝 6. PROPERTY INQUIRY FORM (NEW)

### Form Rendering
- [ ] PropertyInquiryForm Livewire component loads
- [ ] Form displays with fields: Name, Email, Phone, Message, Privacy checkbox
- [ ] Property title displays at top of form (e.g., "Información sobre esta propiedad")

### Form Validation
- [ ] Submit without Name → error "name is required"
- [ ] Submit without Email → error "email is required"
- [ ] Submit with invalid Email → error "email must be a valid email"
- [ ] Submit without Phone → error "phone is required"
- [ ] Submit without privacy checkbox → error "accept_privacy is required"
- [ ] All error messages display below respective fields

### Form Submission
- [ ] Fill all fields with valid data
- [ ] Submit form
- [ ] Loading state displays ("Enviando..." with spinner)
- [ ] Success message displays: "¡Solicitud enviada!" with checkmark icon
- [ ] Success message shows: "Un asesor especializado te contactará en menos de 24 horas."
- [ ] Form fields clear after success

### Spam Protection
- [ ] Honeypot field (`website_url`) hidden
- [ ] Filling honeypot and submitting shows success (honeypot working)
- [ ] reCAPTCHA widget displays (if configured)

### Email Confirmation
- [ ] User receives confirmation email to submitted address
- [ ] Email subject: "Confirmamos tu interés en: [Property Title]"
- [ ] Email displays:
  - [ ] User's name in greeting
  - [ ] Property title
  - [ ] Phone and email confirmation
  - [ ] Next steps message
  - [ ] "Less than 24 hours" timeline
  - [ ] Footer with branding

---

## 🔧 7. DATABASE & MODELS

### ContactSubmission Table
- [ ] Table exists: `php artisan migrate --step=1` (or check migrations)
- [ ] Columns exist: `name`, `email`, `phone`, `message`, `ip_address`, `user_agent`, `utm_source`, `utm_medium`, `utm_campaign`
- [ ] Model `App\Models\ContactSubmission` exists

### Form Submission Storage
- [ ] Submit PropertyInquiryForm
- [ ] Check database: `SELECT * FROM contact_submissions WHERE email = 'test@example.com';`
- [ ] Data is stored correctly (name, email, phone match submission)
- [ ] `ip_address` and `user_agent` populate automatically
- [ ] `created_at` timestamp is current

---

## 🔐 8. SECURITY & PRIVACY

### Privacy Links
- [ ] "/legal/aviso-de-privacidad" route works
- [ ] "/legal/terminos-y-condiciones" route works
- [ ] Links in footer and forms point to correct pages

### Form Security
- [ ] Sensitive data not logged to console
- [ ] No sensitive data in URL parameters
- [ ] reCAPTCHA token sent securely
- [ ] HTTPS enforced (if in production)

---

## 📊 9. FILAMENT ADMIN INTEGRATION

### ContactSubmission Resource (if exists)
- [ ] Navigate to admin panel
- [ ] Find "Contact Submissions" or "Formularios" section
- [ ] View list of submitted forms
- [ ] Click on a submission to view details
- [ ] Data displays correctly (name, email, phone, message, timestamps)
- [ ] Filter by utm_source, utm_medium, utm_campaign (if implemented)

---

## 🌐 10. RESPONSIVE TESTING

### Desktop (1280px+)
- [ ] All sections display in proper layout
- [ ] Text is readable without horizontal scroll
- [ ] Sidebar/right column properly positioned

### Tablet (768px)
- [ ] Cards adjust to 2-column or full-width grid
- [ ] Form doesn't overflow
- [ ] Navigation adjusts

### Mobile (375px)
- [ ] All sections stack vertically
- [ ] Text and buttons are touch-friendly (min 44px height)
- [ ] Forms are usable without zoom
- [ ] Hamburger menu works

### Mobile Landscape (667x375)
- [ ] Layout doesn't break
- [ ] Forms are still usable

---

## 🔍 11. BROWSER COMPATIBILITY

Test in:
- [ ] Chrome (latest)
- [ ] Safari (latest)
- [ ] Firefox (latest)
- [ ] Edge (latest)
- [ ] Safari on iOS
- [ ] Chrome on Android

Check for:
- [ ] No console errors
- [ ] No layout shifts
- [ ] Animations work
- [ ] Forms submit correctly
- [ ] Emails render (test in MailHog or similar)

---

## 📮 12. EMAIL TESTING

### Local Email Testing (if MailHog configured)
- [ ] Visit http://localhost:8025 (or your MailHog port)
- [ ] Submit PropertyInquiryForm
- [ ] Email appears in MailHog inbox
- [ ] Email subject is correct
- [ ] Email HTML renders properly
- [ ] No broken images
- [ ] Links are clickable

### Email Clients (optional)
- [ ] Send test email to personal Gmail account
- [ ] Send test email to Outlook
- [ ] Check rendering in email clients
- [ ] Verify preheader text if included

---

## 🚀 13. PERFORMANCE

- [ ] Page load time < 3s (check DevTools > Network)
- [ ] No JavaScript errors (check Console)
- [ ] No layout shift (CLS < 0.1)
- [ ] Images load lazy and don't block rendering
- [ ] Animations don't cause jank (check Performance tab)

---

## 🐛 14. BUG HUNTING

### Visual Bugs
- [ ] No text overflow or truncation
- [ ] No misaligned elements
- [ ] Colors match design tokens
- [ ] Hover states are consistent

### Functional Bugs
- [ ] Forms submit without errors
- [ ] Navigation doesn't break
- [ ] No infinite loops or hangs
- [ ] Database operations complete successfully

### Data Integrity
- [ ] Form data is sanitized before storage
- [ ] No SQL injection attempts work
- [ ] XSS attempts are blocked

---

## ✅ SIGN-OFF

Once all tests pass, confirm:

- [ ] All form submissions work end-to-end
- [ ] Emails send and render correctly
- [ ] Database records are accurate
- [ ] No console errors or warnings
- [ ] Responsive design works on all breakpoints
- [ ] Animations are smooth and accessible
- [ ] Security measures are in place
- [ ] Documentation is up-to-date

**Tested by**: ________________  
**Date**: ________________  
**Notes**: ________________

---

## 🔗 QUICK LINKS FOR TESTING

- Home: http://localhost:8000/
- Comprar: http://localhost:8000/comprar
- Vender: http://localhost:8000/vende-tu-propiedad
- Desarrolladores: http://localhost:8000/desarrolladores-e-inversionistas
- Admin: http://localhost:8000/admin (if configured)
- MailHog: http://localhost:8025 (if configured)

---

## 📝 NOTES

- All forms use Livewire 4.2.4 for real-time validation
- Spam protection via reCAPTCHA + honeypot
- Email notifications via PropertyInquiryMail (mailable class)
- Database storage via ContactSubmission model
- Responsive grid system using Tailwind CSS 4
- Animations use x-intersect Alpine directive for scroll-reveal
