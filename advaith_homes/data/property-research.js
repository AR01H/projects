// ============================================================
//  Advaith Homes — Property Research Report Data
//  URL: pages/detail.html?page=property-research
// ============================================================

var PROPERTY_RESEARCH_DATA = {
  page: {
    title: "Property Research Report | Advaith Homes",
    metaDescription: "Get a deep analysis of any UK property before you buy. Covering transaction history, boundaries, crime data, and more.",
    
    hero: {
      eyebrow: "Deep Due Diligence",
      headline: "Property Research <em>Report</em>",
      subtext: "Provides an analysis of a specific property, covering details like transaction history, boundaries, local crime data, transport links, nearby schools, and other insights to help buyers assess the property in depth.",
      stats: [
        { num: "50+", label: "Data points analyzed" },
        { num: "24h", label: "Turnaround time" },
        { num: "100%", label: "Unbiased accuracy" }
      ]
    },

    banner: {
      image: "https://images.unsplash.com/photo-1460472178825-e5240623abe5?auto=format&fit=crop&q=80&w=1200",
      title: "Knowledge is Power",
      text: "Don't rely on the seller's agent for information. Get an independent, comprehensive report that reveals the true state of your potential investment."
    },

    testimonials: [
      { text: "The research report uncovered a planned development right behind the garden. Saved me from a huge mistake!", author: "David P.", location: "Richmond" },
      { text: "Invaluable for negotiation. I knocked £15k off the asking price using the comparable sales data.", author: "Emma L.", location: "Bristol" }
    ],

    challengesTitle: "What's Covered",
    challengesHeadline: "Key Research Areas",
    challenges: [
      {
        phase: "Property Data", icon: "🏠",
        items: [
          "Historical transaction prices and dates",
          "Property boundary and title analysis",
          "Planning application history for the site"
        ]
      },
      {
        phase: "Location Risks", icon: "⚠️",
        items: [
          "Local crime statistics and trends",
          "Flood risk and environmental assessments",
          "Flight paths and noise pollution data"
        ]
      }
    ],

    process: {
      tag: "Workflow",
      headline: "How We Build Your Report",
      steps: [
        { title: "Property Identification", desc: "You provide the address or we source it for you." },
        { title: "Deep-Dive Analysis", desc: "Our analysts cross-reference 50+ data points from Land Registry, Ofsted, and more." },
        { title: "Quality Review", desc: "A senior expert verifies every finding for accuracy and leverage points." },
        { title: "Instant Delivery", desc: "Your comprehensive PDF is delivered straight to your inbox." }
      ]
    },

    table: {
      tag: "Report Options",
      headline: "Choose Your Level of Insight",
      columns: ["Features", "Comprehensive Facts Report", "Standard Facts Report", "Basic Facts Report"],
      rows: [
        {
          category: "Comparable Listings",
          items: [
            { name: "Recent Sales", desc: "Sold prices of similar properties nearby", values: [true, true, true] },
            { name: "Available for Sale", desc: "Current listings in the area", values: [true, true, false] },
            { name: "Under Offer / Sale Agreed", values: [true, true, false] },
            { name: "For Rent", values: [true, false, false] }
          ]
        },
        {
          category: "Nearby Schools",
          items: [
            { name: "Ofsted Ratings", values: [true, true, false] },
            { name: "Maps and Distances", values: [true, false, false] },
            { name: "School Catchment Area", values: [true, false, false] }
          ]
        },
        {
          category: "Transport Connections",
          items: [
            { name: "Access Type (Road, Rail, Air)", values: [true, true, false] },
            { name: "Distance Maps", values: [true, false, false] }
          ]
        }
      ]
    },

    whatWeDo: {
      do: [
        "Sift through thousands of public and private records",
        "Verify information provided by estate agents",
        "Highlight potential deal-breakers early",
        "Provide a clear, easy-to-read summary"
      ],
      dont: [
        "Replace a structural survey (though we can recommend one)",
        "Provide legal advice (this is for your solicitor)",
        "Charge extra for 'premium' data points"
      ]
    },

    commonQuestions: [
      { q: "How long does it take to receive my report?", a: "Most reports are delivered via email within 24 hours of your request." },
      { q: "Can I use this report to negotiate a lower price?", a: "Absolutely. Our reports often uncover data points (like over-market pricing or local issues) that serve as powerful leverage." }
    ],

    cta: {
      headline: "Ready for a Deeper Look?",
      subtext: "Order your Property Research Report today and buy with total confidence.",
      buttonText: "Order Your Report →",
      buttonUrl: "../contact.html"
    }
  }
};
