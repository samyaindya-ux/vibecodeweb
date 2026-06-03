export default function Contact() {
  return (
    <section id="contact" className="py-24 px-6 relative">
      <div className="container mx-auto">
        <div className="text-center mb-16">
          <span className="section-label">Let&apos;s Connect</span>
          <h2 className="text-4xl md:text-5xl font-bold font-serif mb-4">
            Ready to Scale{' '}
            <span className="gradient-text">Your Business?</span>
          </h2>
          <p className="text-[#cbd5e1] text-lg max-w-2xl mx-auto">
            Whether you need a cutting-edge POS system, AI automations, or a
            custom web solution, we are here to help. Reach out to us today.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
          {/* Contact info */}
          <div className="space-y-6">
            <a
              href="https://wa.me/919477443425"
              target="_blank"
              rel="noopener noreferrer"
              className="glass-card p-6 flex items-center gap-4 hover:-translate-y-1 transition-transform duration-300 block"
            >
              <div className="w-12 h-12 rounded-xl bg-[#10b981]/20 flex items-center justify-center flex-shrink-0">
                <i className="fab fa-whatsapp text-2xl text-[#10b981]" />
              </div>
              <div>
                <p className="text-[#cbd5e1] text-xs uppercase tracking-wider mb-1">
                  WhatsApp / Phone
                </p>
                <p className="text-[#f8fafc] font-semibold">+91 9477443425</p>
              </div>
            </a>

            <a
              href="mailto:samya.indya@gmail.com"
              className="glass-card p-6 flex items-center gap-4 hover:-translate-y-1 transition-transform duration-300 block"
            >
              <div className="w-12 h-12 rounded-xl bg-[#3b82f6]/20 flex items-center justify-center flex-shrink-0">
                <i className="fas fa-envelope text-xl text-[#3b82f6]" />
              </div>
              <div>
                <p className="text-[#cbd5e1] text-xs uppercase tracking-wider mb-1">
                  Email
                </p>
                <p className="text-[#f8fafc] font-semibold">
                  samya.indya@gmail.com
                </p>
              </div>
            </a>
          </div>

          {/* Form */}
          <form
            action="https://formsubmit.co/samya.indya@gmail.com"
            method="POST"
            className="glass-card p-8 space-y-5"
          >
            <input type="hidden" name="_captcha" value="false" />
            <input
              type="hidden"
              name="_subject"
              value="New message from VibeCodeWeb"
            />

            <div>
              <label
                htmlFor="name"
                className="block text-[#cbd5e1] text-sm mb-2"
              >
                Your Name
              </label>
              <input
                type="text"
                id="name"
                name="name"
                required
                className="w-full bg-[#0f172a]/60 border border-[#334155]/50 rounded-xl px-4 py-3 text-[#f8fafc] text-sm placeholder-[#cbd5e1]/40 focus:outline-none focus:border-[#3b82f6]/60 transition-colors"
                placeholder="Enter your name"
              />
            </div>

            <div>
              <label
                htmlFor="email"
                className="block text-[#cbd5e1] text-sm mb-2"
              >
                Your Email
              </label>
              <input
                type="email"
                id="email"
                name="email"
                required
                className="w-full bg-[#0f172a]/60 border border-[#334155]/50 rounded-xl px-4 py-3 text-[#f8fafc] text-sm placeholder-[#cbd5e1]/40 focus:outline-none focus:border-[#3b82f6]/60 transition-colors"
                placeholder="Enter your email"
              />
            </div>

            <div>
              <label
                htmlFor="message"
                className="block text-[#cbd5e1] text-sm mb-2"
              >
                Your Message
              </label>
              <textarea
                id="message"
                name="message"
                required
                rows={4}
                className="w-full bg-[#0f172a]/60 border border-[#334155]/50 rounded-xl px-4 py-3 text-[#f8fafc] text-sm placeholder-[#cbd5e1]/40 focus:outline-none focus:border-[#3b82f6]/60 transition-colors resize-none"
                placeholder="Tell us about your project..."
              />
            </div>

            <button
              type="submit"
              className="w-full py-4 rounded-xl bg-gradient-to-r from-[#f97316] to-[#3b82f6] text-white font-semibold hover:shadow-lg hover:shadow-[#3b82f6]/30 hover:-translate-y-0.5 transition-all"
            >
              <i className="fas fa-paper-plane mr-2" />
              Send Message
            </button>
          </form>
        </div>
      </div>
    </section>
  );
}
