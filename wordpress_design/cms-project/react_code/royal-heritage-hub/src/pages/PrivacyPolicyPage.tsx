import { StaticPage } from './StaticPage';

export default function PrivacyPolicyPage() {
  return (
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
      <p>For questions about your data, contact royalheritagehub@gmail.com.</p>
    </StaticPage>
  );
}
