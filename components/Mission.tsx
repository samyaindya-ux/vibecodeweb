import Image from 'next/image';

const IMG_BASE =
  'https://raw.githubusercontent.com/samyaindya-ux/vibecodeweb/main/images';

export default function Mission() {
  return (
    <section id="about" className="py-24 px-6 bg-[#0f172a]/30 relative">
      <div className="container mx-auto grid md:grid-cols-2 gap-16 items-center">
        <div className="order-2 md:order-1">
          <div className="glass-card p-4 relative">
            <Image
              src={`${IMG_BASE}/founder_portrait.png`}
              alt="Founder"
              width={600}
              height={500}
              className="rounded-xl object-cover w-full"
            />
            <div className="absolute bottom-8 left-1/2 -translate-x-1/2 bg-[#0f172a]/80 backdrop-blur-sm border border-[#334155]/50 rounded-xl px-6 py-3 text-center whitespace-nowrap">
              <p className="text-[#10b981] text-xs font-semibold">AI-Powered Growth</p>
              <p className="text-[#cbd5e1] text-xs">Scaling Businesses Globally</p>
            </div>
          </div>
        </div>

        <div className="order-1 md:order-2">
          <span className="section-label">Our Mission</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif mb-6">
            Elevating Businesses,{' '}
            <span className="gradient-text">Setting New Benchmarks</span>
          </h2>
          <p className="text-[#cbd5e1] text-lg leading-relaxed mb-10">
            By blending global best practices with cutting-edge AI technologies,
            we empower businesses of all sizes—from ambitious startups to large
            enterprises—to reach and exceed their industry benchmarks. Whether
            it&apos;s a breathtaking landing page or a complex AI-driven
            workflow, our intelligent solutions are designed to scale your
            success.
          </p>

          <div className="grid grid-cols-2 gap-4">
            <div className="glass-card p-5 text-center">
              <i className="fas fa-bolt text-2xl text-[#f97316] mb-3 block" />
              <h4 className="font-bold text-[#f8fafc] mb-1">Fast Delivery</h4>
              <p className="text-[#cbd5e1] text-xs">Streamlined agile workflows</p>
            </div>
            <div className="glass-card p-5 text-center">
              <i className="fas fa-brain text-2xl text-[#3b82f6] mb-3 block" />
              <h4 className="font-bold text-[#f8fafc] mb-1">AI Integrated</h4>
              <p className="text-[#cbd5e1] text-xs">Powered by the latest LLMs</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
