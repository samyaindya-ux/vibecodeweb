import Image from 'next/image';

const IMG_BASE =
  'https://raw.githubusercontent.com/samyaindya-ux/vibecodeweb/main/images';

const advantages = [
  {
    icon: 'fa-clock',
    title: '24/7 Operational Efficiency',
    desc: 'AI never sleeps. Automated customer service, lead generation, and data processing run around the clock without extra overhead.',
  },
  {
    icon: 'fa-bullseye',
    title: 'Hyper-Personalization',
    desc: 'Deliver uniquely tailored experiences, product recommendations, and marketing to every single customer automatically.',
  },
  {
    icon: 'fa-wallet',
    title: 'Massive Cost Reduction',
    desc: 'Reduce manual labor hours on repetitive tasks by up to 80%, allowing your team to focus on high-value creative and strategic work.',
  },
];

export default function AIAdvantages() {
  return (
    <section
      id="ai-advantages"
      className="py-24 px-6 bg-[#0f172a]/30 relative"
    >
      <div className="container mx-auto">
        <div className="text-center mb-16">
          <span className="section-label">Core Advantages</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif">
            Unfair Advantages of{' '}
            <span className="gradient-text">Using AI</span>
          </h2>
        </div>

        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div className="space-y-6">
            {advantages.map((adv, i) => (
              <div
                key={i}
                className="glass-card p-6 flex gap-4 hover:-translate-y-1 transition-transform duration-300"
              >
                <div className="w-12 h-12 rounded-xl flex items-center justify-center bg-[#3b82f6]/20 text-[#3b82f6] flex-shrink-0">
                  <i className={`fas ${adv.icon} text-xl`} />
                </div>
                <div>
                  <h3 className="font-bold text-[#f8fafc] mb-2">{adv.title}</h3>
                  <p className="text-[#cbd5e1] text-sm leading-relaxed">{adv.desc}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="glass-card p-4">
            <Image
              src={`${IMG_BASE}/ai_advantages_dashboard.png`}
              alt="Data-Driven Precision"
              width={600}
              height={400}
              className="rounded-xl object-cover w-full"
            />
            <p className="text-center text-[#cbd5e1] text-sm mt-3">
              Data-Driven Precision
            </p>
          </div>
        </div>
      </div>
    </section>
  );
}
