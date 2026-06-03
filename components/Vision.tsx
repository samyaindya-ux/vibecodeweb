export default function Vision() {
  return (
    <section id="vision" className="py-24 px-6 relative">
      <div className="container mx-auto">
        <div className="text-center mb-16">
          <span className="section-label">Vision & Advantage</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif">
            Taking India Forward{' '}
            <span className="gradient-text">with AI</span>
          </h2>
        </div>

        <div className="grid md:grid-cols-2 gap-8">
          <div className="glass-card p-8 hover:-translate-y-1 transition-transform duration-300">
            <div className="w-14 h-14 rounded-2xl bg-[#f97316]/20 flex items-center justify-center mb-6">
              <i className="fas fa-dharmachakra text-2xl text-[#f97316]" />
            </div>
            <h3 className="text-2xl font-bold font-serif mb-4">Our Goal</h3>
            <p className="text-[#cbd5e1] leading-relaxed">
              To empower local businesses, startups, and enterprises across India
              by democratizing access to cutting-edge AI technologies. We believe
              that by integrating smart automations and next-gen web solutions, we
              can elevate Indian businesses to compete—and win—on a global stage.
              We are committed to building digital infrastructure that is not just
              functional, but revolutionary, taking India forward in the global
              tech landscape.
            </p>
          </div>

          <div className="glass-card p-8 hover:-translate-y-1 transition-transform duration-300">
            <div className="w-14 h-14 rounded-2xl bg-[#3b82f6]/20 flex items-center justify-center mb-6">
              <i className="fas fa-shield-halved text-2xl text-[#3b82f6]" />
            </div>
            <h3 className="text-2xl font-bold font-serif mb-4">Our Moat</h3>
            <p className="text-[#cbd5e1] leading-relaxed">
              Our unfair advantage lies in the mastery of industry-leading AI
              tools like ChatGPT, Claude, and Gemini. By heavily utilizing AI in
              our workflow, we dramatically reduce development time and costs. This
              means we can deliver complex, hyper-optimized, and highly scalable
              solutions faster and more affordably than traditional agencies. Our
              tech stack is leaner, smarter, and strictly focused on ROI for your
              business.
            </p>
          </div>
        </div>
      </div>
    </section>
  );
}
