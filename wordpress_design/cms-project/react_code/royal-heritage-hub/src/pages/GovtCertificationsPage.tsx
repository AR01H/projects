import { useEffect, useState } from 'react';
import { certificationsApi } from '@/api/certifications';
import { PageHero } from '@/components/common/PageHero';
import { Breadcrumbs } from '@/components/common/Breadcrumbs';
import { Reveal } from '@/components/common/Reveal';
import { Badge } from '@/components/common/Badge';
import type { CertificationEntry } from '@/types/product';

export default function GovtCertificationsPage() {
  const [certifications, setCertifications] = useState<CertificationEntry[] | null>(null);

  useEffect(() => {
    certificationsApi.getAll().then(setCertifications);
  }, []);

  return (
    <div>
      <PageHero pageKey="faqs" fallbackTitle="Government Certifications" />

      <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <Breadcrumbs items={[{ label: 'Certifications' }]} />

        <div className="mb-12 text-center">
          <p className="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-secondary-dark)]">
            Verified & Compliant
          </p>
          <h1 className="font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">
            Government Certifications
          </h1>
          <p className="mx-auto mt-3 max-w-xl text-sm text-[var(--color-text-secondary)]">
            Royal Heritage Hub operates in full compliance with Indian government
            regulations. Below are our official certifications and registrations.
          </p>
        </div>

        {certifications === null ? (
          <p className="text-center text-sm text-[var(--color-text-muted)]">Loading...</p>
        ) : (
          <div className="flex flex-col gap-16">
            {certifications.map((cert, i) => (
              <Reveal key={cert.id} delay={Math.min(i * 100, 300)}>
                <div
                  className={`grid grid-cols-1 items-center gap-8 lg:grid-cols-2 lg:gap-12 ${
                    cert.imageSide === 'right' ? 'lg:[&>*:first-child]:order-2' : ''
                  }`}
                >
                  <div className="overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-card)]">
                    <img
                      src={cert.image}
                      alt={cert.title}
                      loading="lazy"
                      className="aspect-[4/3] w-full object-cover"
                    />
                  </div>
                  <div>
                    <Badge variant="gold">Certified</Badge>
                    <h2 className="mt-3 font-display text-xl text-[var(--color-text-primary)] sm:text-2xl">
                      {cert.title}
                    </h2>
                    <p className="mt-1 text-sm font-medium text-[var(--color-primary)]">{cert.issuedBy}</p>
                    <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[var(--color-text-muted)]">
                      {cert.certificateNumber && <span>Certificate No: {cert.certificateNumber}</span>}
                      {cert.date && <span>Issued: {cert.date}</span>}
                    </div>
                    <p className="mt-4 text-sm leading-relaxed text-[var(--color-text-secondary)]">
                      {cert.description}
                    </p>
                  </div>
                </div>
              </Reveal>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
