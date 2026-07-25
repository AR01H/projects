import { StaticPage } from './StaticPage';
import { SEO } from '@/components/common/SEO';
import { SITE_CONFIG } from '@/config/site';

export default function ReturnPolicyPage() {
  return (
    <>
      <SEO title="Return Policy" description="Learn about our return and exchange policy for handcrafted products at Royal Heritage Hub." />
      <StaticPage title="Return Policy">
      <p>
        We want you to love your handcrafted piece. If something isn't right, most items are eligible
        for return or exchange within 7 days of delivery, provided they are unused and in their original packaging.
      </p>
      <p>
        Because each product is handmade, minor variations in color, grain, or finish are natural
        characteristics of the craft and are not considered defects.
      </p>
      <p>
        Customised or personalised items (such as engraved name plates) are not eligible for return
        unless damaged in transit.
      </p>
      <p>
        To start a return, contact us at <a href={SITE_CONFIG.contact.emailHref} className="text-[var(--color-primary)] underline">{SITE_CONFIG.contact.email}</a> with your order number and photos
        of the item.
      </p>
      </StaticPage>
    </>
  );
}
