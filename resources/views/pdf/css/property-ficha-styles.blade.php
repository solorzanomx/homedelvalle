/* PREMIUM PROPERTY FICHA - INLINE STYLES FOR DOMPDF */

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  color: #1e293b;
  line-height: 1.6;
  background: #ffffff;
}

/* PAGE SETUP */
@page {
  size: A4 portrait;
  margin: 0;
  padding: 0;
}

body {
  width: 210mm;
  height: 297mm;
  margin: 0;
  padding: 0;
}

/* TYPOGRAPHY */
h1 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 36px;
  font-weight: bold;
  color: #0f172a;
  line-height: 1.2;
  margin-bottom: 15px;
}

h2 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 18px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 20px;
  margin-top: 30px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #0f172a;
  padding-bottom: 10px;
}

p {
  font-size: 11px;
  line-height: 1.7;
  margin-bottom: 12px;
  color: #1e293b;
}

/* CONTAINER & LAYOUT */
.ficha-container {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.ficha-page {
  width: 100%;
  min-height: 297mm;
  display: flex;
  flex-direction: column;
  page-break-after: always;
  position: relative;
}

.ficha-page:last-child {
  page-break-after: avoid;
}

.ficha-content {
  flex: 1;
  padding: 50px 40px;
  background: #ffffff;
}

/* HEADER SECTION */
.ficha-header {
  background: #ffffff;
  padding: 30px 40px;
  border-bottom: 1px solid #cbd5e1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-logo {
  width: 120px;
  height: auto;
}

.header-logo img {
  width: 100%;
  height: auto;
  display: block;
}

.header-info {
  text-align: right;
  font-size: 10px;
  color: #64748b;
  line-height: 1.5;
}

.header-info p {
  margin: 0;
  font-size: 10px;
}

/* HERO SECTION */
.hero-section {
  position: relative;
  width: 100%;
  height: 280px;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  overflow: hidden;
  margin-bottom: 30px;
  border-radius: 2px;
}

.hero-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.hero-image.placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.1);
  font-size: 12px;
}

.hero-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0) 100%);
  padding: 30px 40px 25px;
  color: #ffffff;
}

.hero-badges {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  flex-wrap: wrap;
}

.hero-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 9px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  backdrop-filter: blur(10px);
}

.hero-title {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 28px;
  font-weight: bold;
  color: #ffffff;
  margin-bottom: 8px;
  line-height: 1.2;
}

.hero-location {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.8);
}

.hero-price-section {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: baseline;
}

.hero-price {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 42px;
  font-weight: bold;
  color: #ffffff;
  line-height: 1;
}

.hero-price-label {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.7);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* SPECS SECTION */
.specs-section {
  margin-bottom: 30px;
}

.specs-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.spec-card {
  background: #f8fafc;
  padding: 18px;
  border-radius: 2px;
  text-align: center;
  border-top: 3px solid #0f172a;
  transition: all 0.3s ease;
}

.spec-card:hover {
  background: #f1f5f9;
}

.spec-value {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 22px;
  font-weight: bold;
  color: #0f172a;
  display: block;
  margin-bottom: 6px;
}

.spec-label {
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  font-weight: 600;
}

/* DESCRIPTION SECTION */
.description-section {
  margin-bottom: 30px;
  padding: 25px;
  background: #f8fafc;
  border-left: 4px solid #0f172a;
  border-radius: 0 2px 2px 0;
}

.description-section h2 {
  margin-top: 0;
}

.description-section p {
  font-size: 11px;
  line-height: 1.8;
  color: #1e293b;
  margin-bottom: 12px;
}

/* DETAILS TABLE */
.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-top: 20px;
  background: #ffffff;
  padding: 20px;
  border: 1px solid #e2e8f0;
  border-radius: 2px;
}

.detail-item {
  padding: 12px 0;
  border-bottom: 1px solid #e2e8f0;
}

.detail-item:nth-child(even) {
  padding-left: 20px;
}

.detail-label {
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  font-weight: 600;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 12px;
  font-weight: 600;
  color: #0f172a;
}

/* AMENITIES SECTION */
.amenities-section {
  margin-bottom: 30px;
}

.amenities-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
}

.amenity-item {
  background: #f8fafc;
  padding: 10px 15px;
  border-left: 3px solid #2563eb;
  border-radius: 0 2px 2px 0;
  font-size: 11px;
  color: #1e293b;
  display: flex;
  align-items: center;
}

.amenity-item:before {
  content: '✓';
  color: #2563eb;
  font-weight: bold;
  margin-right: 8px;
}

/* GALLERY SECTION */
.gallery-section {
  margin-bottom: 30px;
  page-break-inside: avoid;
}

.gallery-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.gallery-grid.three-col {
  grid-template-columns: 1fr 1fr 1fr;
}

.gallery-grid.four-col {
  grid-template-columns: 1fr 1fr 1fr 1fr;
}

.gallery-item {
  width: 100%;
  height: 200px;
  overflow: hidden;
  border-radius: 2px;
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.gallery-item.placeholder {
  color: #94a3b8;
  font-size: 10px;
}

/* QR SECTION */
.qr-section {
  margin: 30px 0;
  padding: 30px;
  background: #f8fafc;
  border-radius: 2px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  page-break-inside: avoid;
}

.qr-container {
  text-align: center;
}

.qr-code {
  width: 200px;
  height: 200px;
  margin: 0 auto 15px;
  background: #ffffff;
  padding: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-code img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.qr-label {
  font-size: 11px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  font-weight: 600;
  margin-top: 10px;
}

/* BROKER SECTION */
.broker-section {
  margin: 30px 0;
  padding: 25px;
  background: #0f172a;
  border-radius: 2px;
  color: #ffffff;
  page-break-inside: avoid;
}

.broker-layout {
  display: flex;
  gap: 25px;
  align-items: flex-start;
}

.broker-photo {
  flex-shrink: 0;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.broker-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.broker-photo.placeholder {
  font-size: 48px;
  font-weight: bold;
  color: #ffffff;
  font-family: Georgia, serif;
}

.broker-info {
  flex: 1;
  padding-top: 5px;
}

.broker-title {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 8px;
}

.broker-name {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 18px;
  font-weight: bold;
  color: #ffffff;
  margin-bottom: 12px;
  line-height: 1.2;
}

.broker-contact {
  font-size: 11px;
  line-height: 1.8;
  color: rgba(255, 255, 255, 0.85);
}

.broker-contact-item {
  margin-bottom: 6px;
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.broker-contact-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 600;
  width: 50px;
  flex-shrink: 0;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.2px;
}

.broker-contact-value {
  color: #ffffff;
  flex: 1;
  word-break: break-word;
}

/* FOOTER SECTION */
.ficha-footer {
  background: #1e293b;
  color: #cbd5e1;
  padding: 25px 40px;
  font-size: 10px;
  border-top: 1px solid #334155;
  margin-top: auto;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.footer-left {
  flex: 1;
}

.footer-logo {
  width: 80px;
  height: auto;
  margin-bottom: 12px;
}

.footer-logo img {
  width: 100%;
  height: auto;
  display: block;
  opacity: 0.8;
}

.footer-company-info {
  font-size: 9px;
  line-height: 1.6;
  color: #94a3b8;
}

.footer-company-info p {
  margin: 0 0 5px;
  font-size: 9px;
  color: #94a3b8;
}

.footer-center {
  flex: 1;
  text-align: center;
}

.footer-center p {
  margin: 0;
  font-size: 9px;
  color: #94a3b8;
  line-height: 1.6;
}

.footer-right {
  flex: 1;
  text-align: right;
}

.footer-legal {
  font-size: 9px;
  color: #64748b;
  line-height: 1.5;
  margin-top: 10px;
}

/* UTILITY CLASSES */
.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

.mt-20 {
  margin-top: 20px;
}

.mb-20 {
  margin-bottom: 20px;
}

.page-break {
  page-break-after: always;
  height: 0;
}

.hidden {
  display: none !important;
}

.visible {
  display: block !important;
}

/* RESPONSIVE ADJUSTMENTS FOR NARROW GRIDS */
@media print {
  body {
    background: #ffffff;
  }

  .specs-grid {
    grid-template-columns: 1fr 1fr;
  }

  .amenities-grid {
    grid-template-columns: 1fr 1fr;
  }

  .gallery-grid {
    grid-template-columns: 1fr 1fr;
  }
}

/* PRINT-SPECIFIC */
@page {
  margin: 0;
}

body {
  margin: 0;
  padding: 0;
}

.ficha-page {
  page-break-after: always;
}

.ficha-page:last-child {
  page-break-after: avoid;
}
