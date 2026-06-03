const plans = [
  {
    name: 'Starter Web',
    price: '₹5000',
    suffix: 'onwards',
    features: ['Custom Landing Page', 'Mobile Responsive', 'Contact Form', 'Basic SEO'],
    cta: 'Get Started',
    popular: false,
    href: 'https://wa.me/919477443425?text=Hi%2C%20I%20am%20interested%20in%20the%20Starter%20Web%20plan.',
  },
  {
    name: 'Pro AI Business',
    price: '₹10000',
    suffix: 'onwards',
    features: [
      'Complete Website Setup',
      'AI Chatbot Integration',
      'Retails & POS System',
      'Advanced SEO',
      'Free Domain (1yr)',
    ],
    cta: 'Go Pro',
    popular: true,
    href: 'https://wa.me/919477443425?text=Hi%2C%20I%20am%20interested%20in%20the%20Pro%20AI%20Business%20plan.',
  },
  {
    name: 'Custom App',
    price: "Let's Talk",
    suffix: '',
    features: [
      'Complex Web Apps',
      'Custom AI Models',
      'E-Commerce Solutions',
      'Dedicated Support',
    ],
    cta: 'Contact Us',
    popular: false,
    href: 'https://wa.me/919477443425?text=Hi%2C%20I%20would%20like%20to%20discuss%20a%20Custom%20App.',
  },
];

export default function Pricing() {
  return (
    <section id="pricing" className="py-24 px-6 bg-[#0f172a]/30 relative">
      <div className="container mx-auto">
        <div className="text-center mb-16">
          <span className="section-label">Investment</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif mb-4">
            Simple, Transparent{' '}
            <span className="gradient-text">Pricing</span>
          </h2>
          <p className="text-[#cbd5e1] text-lg">
            Get world-class AI and Web solutions tailored for your business
            without the enterprise price tag.
          </p>
        </div>

        <div className="grid md:grid-cols-3 gap-8 items-start max-w-5xl mx-auto">
          {plans.map((plan, i) => (
            <div
              key={i}
              className={`glass-card p-8 flex flex-col relative ${
                plan.popular
                  ? 'border-[#3b82f6]/60 shadow-2xl shadow-[#3b82f6]/20 md:scale-105'
                  : ''
              }`}
            >
              {plan.popular && (
                <div className="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-1.5 rounded-full bg-gradient-to-r from-[#f97316] to-[#3b82f6] text-white text-xs font-bold uppercase tracking-wider whitespace-nowrap">
                  Most Popular
                </div>
              )}

              <h3 className="text-xl font-bold font-serif mb-2">{plan.name}</h3>
              <div className="mb-6">
                <span className="text-4xl font-bold gradient-text">{plan.price}</span>
                {plan.suffix && (
                  <span className="text-[#cbd5e1] text-sm ml-2">{plan.suffix}</span>
                )}
              </div>

              <ul className="space-y-3 mb-8 flex-1">
                {plan.features.map((feat, j) => (
                  <li key={j} className="flex items-center gap-3 text-[#cbd5e1] text-sm">
                    <i className="fas fa-check text-[#10b981] text-xs" />
                    {feat}
                  </li>
                ))}
              </ul>

              <a
                href={plan.href}
                target="_blank"
                rel="noopener noreferrer"
                className={`block w-full py-3 rounded-xl text-center font-semibold transition-all ${
                  plan.popular
                    ? 'bg-gradient-to-r from-[#f97316] to-[#3b82f6] text-white hover:shadow-lg hover:shadow-[#3b82f6]/30'
                    : 'border border-[#334155]/50 text-[#f8fafc] hover:bg-[#1e293b]'
                }`}
              >
                {plan.cta}
              </a>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
