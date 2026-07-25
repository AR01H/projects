import { StaticPage } from './StaticPage';
import { SEO } from '@/components/common/SEO';
import { SITE_CONFIG } from '@/config/site';

export default function PrivacyPolicyPage() {
  return (
    <>
      <SEO title="Privacy Policy" description="Learn how Royal Heritage Hub respects and protects your privacy and personal data." />
      <StaticPage title="Privacy Policy">
        <p>
          Royal Heritage Hub respects your privacy. We collect only the information necessary to
          process your orders — name, address, contact details, and payment information — and never
          sell your data to third parties.
        </p>
        <p>
          Payment information is processed securely through our payment partners and is never stored
          on our servers.
        </p>
        <p>
          We may use your email to send order updates and, if you've opted in, occasional newsletters.
          You can unsubscribe at any time.
        </p>
        <p>For questions about your data, contact <a href={SITE_CONFIG.contact.emailHref} className="text-[var(--color-primary)] underline">{SITE_CONFIG.contact.email}</a>.</p>
      </StaticPage>
    </>
  );
}
