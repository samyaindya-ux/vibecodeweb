const services = [
  {
    icon: 'fa-cash-register',
    title: 'Retails & POS System',
    desc: 'Providing smart, efficient, and reliable Point of Sale and retail management solutions for your business.',
  },
  {
    icon: 'fa-robot',
    title: 'AI Automations',
    desc: 'Streamlining workflows and scaling operations using cutting-edge AI technologies and custom tool integrations.',
  },
  {
    icon: 'fa-file-lines',
    title: 'Document Analyzer',
    desc: 'Extracting actionable insights, summarizing complex data, and organizing unstructured documents using advanced AI models.',
  },
  {
    icon: 'fa-cloud',
    title: 'SaaS Development',
    desc: 'Developing scalable, cloud-based Software as a Service products with seamless user experiences and robust backends.',
  },
  {
    icon: 'fa-pen-nib',
    title: 'Niche Content Generator',
    desc: 'Automating the creation of highly specialized, engaging content tailored to your unique audience and brand voice.',
  },
  {
    icon: 'fa-network-wired',
    title: 'AI Strategy & Consulting',
    desc: 'Providing expert guidance on adopting AI strategies tailored for your business to outpace the competition.',
  },
];

export default function Services() {
  return (
    <section id="services" className="py-24 px-6 relative">
      <div className="container mx-auto">
        <div className="text-center mb-16">
          <span className="section-label">Capabilities</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif">
            Solutions Designed{' '}
            <span className="gradient-text">to Scale</span>
          </h2>
        </div>

        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((svc, i) => (
            <div
              key={i}
              className="glass-card p-8 group hover:-translate-y-2 hover:border-[#3b82f6]/50 transition-all duration-300"
            >
              <div className="w-14 h-14 rounded-2xl bg-[#3b82f6]/10 flex items-center justify-center mb-6 group-hover:bg-[#3b82f6]/20 transition-colors">
                <i className={`fas ${svc.icon} text-2xl text-[#3b82f6]`} />
              </div>
              <h3 className="text-xl font-bold font-serif mb-3">{svc.title}</h3>
              <p className="text-[#cbd5e1] text-sm leading-relaxed">{svc.desc}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
