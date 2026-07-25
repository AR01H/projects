import { StaticPage } from './StaticPage';
import { SEO } from '@/components/common/SEO';

export default function TermsPage() {
  return (
    <>
      <SEO title="Terms & Conditions" description="Read the terms and conditions for using Royal Heritage Hub's website and services." />
      <StaticPage title="Terms & Conditions">
        <p>By using Royal Heritage Hub, you agree to the following terms.</p>
        <p>
          All product descriptions, images, and pricing are subject to change without notice. We
          reserve the right to limit quantities and refuse orders at our discretion.
        </p>
        <p>
          Handmade items may show natural variation from the photos shown, as each piece is
          individually crafted.
        </p>
        <p>
          All content on this site — including text, images, and branding — is the property of
          Royal Heritage Hub and may not be reproduced without permission.
        </p>
      </StaticPage>
    </>
  );
}
