export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="bg-[#0f172a] border-t border-[#334155]/30 py-12 px-6 relative overflow-hidden">
      <div className="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
        <i className="fas fa-code text-[20rem] text-[#3b82f6]" />
      </div>

      <div className="container mx-auto relative z-10">
        <div className="flex flex-col md:flex-row justify-between items-center gap-8">
          <div className="text-center md:text-left">
            <h3 className="text-2xl font-bold font-serif mb-2">
              VibeCode<span className="gradient-text">Web.in</span>
            </h3>
            <p className="text-[#cbd5e1] text-sm">
              © {year} VibeCodeWeb.in. Proudly built in India.
            </p>
          </div>

          <div className="flex items-center gap-4">
            <a
              href="mailto:samya.indya@gmail.com"
              className="w-10 h-10 rounded-full glass-card flex items-center justify-center text-[#cbd5e1] hover:text-[#3b82f6] hover:border-[#3b82f6]/50 transition-all"
              aria-label="Email"
            >
              <i className="fas fa-envelope" />
            </a>
            <a
              href="https://wa.me/919477443425"
              target="_blank"
              rel="noopener noreferrer"
              className="w-10 h-10 rounded-full glass-card flex items-center justify-center text-[#cbd5e1] hover:text-[#10b981] hover:border-[#10b981]/50 transition-all"
              aria-label="WhatsApp"
            >
              <i className="fab fa-whatsapp" />
            </a>
          </div>
        </div>
      </div>
    </footer>
  );
}
