import { StaticPage } from './StaticPage';
import { SHIPPING } from '@/config/constants';
import { useFormatCurrency } from '@/utils/formatCurrency';
import { SEO } from '@/components/common/SEO';

export default function ShippingPolicyPage() {
  const formatCurrency = useFormatCurrency();
  return (
    <>
      <SEO title="Shipping Policy" description="Learn about shipping charges, delivery times, and Cash on Delivery options at Royal Heritage Hub." />
      <StaticPage title="Shipping Policy">
      <p>We ship across India via trusted logistics partners, with careful packaging for every handcrafted piece.</p>
      <p>
        Orders above {formatCurrency(SHIPPING.freeShippingThreshold)} qualify for free shipping. Orders
        below this amount incur a shipping charge calculated at checkout based on your location and order weight.
      </p>
      <p>
        Estimated delivery time is {SHIPPING.estimatedDeliveryMin}–{SHIPPING.estimatedDeliveryMax} business days
        from the date of dispatch, depending on your location.
      </p>
      <p>Cash on Delivery orders carry an additional charge of {formatCurrency(SHIPPING.codCharge)}.</p>
      </StaticPage>
    </>
  );
}
