import Image from 'next/image';

const IMG_BASE =
  'https://raw.githubusercontent.com/samyaindya-ux/vibecodeweb/main/images';

export default function Hero() {
  return (
    <section className="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0">
        <Image
          src={`${IMG_BASE}/tech_mandala_bg.png`}
          alt=""
          fill
          className="object-cover opacity-10"
          priority
        />
        <div className="absolute inset-0 bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#020617]" />
      </div>

      {/* Glowing orbs */}
      <div
        className="absolute top-1/4 left-1/4 w-96 h-96 rounded-full blur-3xl pointer-events-none animate-pulse-glow"
        style={{ backgroundColor: 'rgba(249,115,22,0.08)' }}
      />
      <div
        className="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full blur-3xl pointer-events-none animate-pulse-glow"
        style={{ backgroundColor: 'rgba(59,130,246,0.08)', animationDelay: '1s' }}
      />

      <div className="relative container mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        {/* Text */}
        <div className="text-center md:text-left">
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#1e293b]/60 border border-[#334155]/50 text-[#cbd5e1] text-sm mb-8">
            <i className="fas fa-location-dot text-[#f97316]" />
            Proudly Built in India
          </div>

          <h1 className="text-5xl md:text-7xl font-bold font-serif leading-tight mb-6">
            Next-Gen{' '}
            <span className="gradient-text">AI Solutions</span>
          </h1>

          <p className="text-[#cbd5e1] text-lg md:text-xl mb-10 max-w-lg mx-auto md:mx-0">
            Custom web development, smart automations, and strategic AI
            consulting—empower your business to reach new heights.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
            <a
              href="https://wa.me/919477443425"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-gradient-to-r from-[#10b981] to-[#3b82f6] text-white font-semibold hover:shadow-lg hover:shadow-[#10b981]/30 hover:-translate-y-0.5 transition-all"
            >
              <i className="fab fa-whatsapp text-xl" /> Chat on WhatsApp
            </a>
            <a
              href="#services"
              className="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full border border-[#334155]/50 text-[#f8fafc] font-semibold hover:bg-[#1e293b]/60 transition-all"
            >
              Explore Services <i className="fas fa-arrow-down text-sm" />
            </a>
          </div>
        </div>

        {/* Logo image */}
        <div className="flex justify-center">
          <div className="animate-float">
            <Image
              src={`${IMG_BASE}/new_site_logo_transparent.png`}
              alt="VibeCodeWeb"
              width={450}
              height={450}
              className="object-contain drop-shadow-2xl"
              priority
            />
          </div>
        </div>
      </div>
    </section>
  );
}
