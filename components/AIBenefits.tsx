import Image from 'next/image';

const IMG_BASE =
  'https://raw.githubusercontent.com/samyaindya-ux/vibecodeweb/main/images';

export default function AIBenefits() {
  return (
    <section id="ai-benefits" className="py-24 px-6 relative">
      <div className="container mx-auto grid md:grid-cols-2 gap-16 items-center">
        <div>
          <span className="section-label">The AI Revolution</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif mb-6">
            How AI Will Benefit{' '}
            <span className="gradient-text">Every Business</span>
          </h2>
          <p className="text-[#cbd5e1] text-lg leading-relaxed mb-8">
            Artificial Intelligence is no longer just for tech giants. It is a
            fundamental shift in how businesses operate, communicate, and scale.
            From local retail shops to massive enterprises, AI acts as an
            invisible force multiplier. By integrating smart automations,
            businesses can dramatically reduce operational costs, eliminate human
            error in repetitive tasks, and unlock insights from data that were
            previously invisible. AI isn&apos;t here to replace your business;
            it&apos;s here to make it unstoppable.
          </p>
          <div className="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-[#10b981]/10 border border-[#10b981]/30 text-[#10b981] font-semibold">
            <i className="fas fa-arrow-trend-up" />
            10x Growth Potential with Smart Workflows
          </div>
        </div>

        <div className="glass-card p-4">
          <Image
            src={`${IMG_BASE}/ai_business_benefit.png`}
            alt="Future-Proof Your Business"
            width={600}
            height={400}
            className="rounded-xl object-cover w-full"
          />
          <p className="text-center text-[#cbd5e1] text-sm mt-3">
            Future-Proof Your Business
          </p>
        </div>
      </div>
    </section>
  );
}
