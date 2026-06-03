'use client';
import { useState, useEffect } from 'react';
import Image from 'next/image';

const IMG_BASE =
  'https://raw.githubusercontent.com/samyaindya-ux/vibecodeweb/main/images';

const navLinks = [
  { href: '#ai-benefits', label: 'AI Benefits' },
  { href: '#ai-advantages', label: 'AI Advantages' },
  { href: '#vision', label: 'Vision' },
  { href: '#about', label: 'About' },
  { href: '#services', label: 'Services' },
  { href: '#pricing', label: 'Pricing' },
  { href: '#contact', label: 'Contact' },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 50);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return (
    <nav
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled
          ? 'bg-[#0f172a]/90 backdrop-blur-md border-b border-[#334155]/30 py-3'
          : 'py-5'
      }`}
    >
      <div className="container mx-auto px-6 flex items-center justify-between">
        <a href="#" className="flex items-center gap-2">
          <Image
            src={`${IMG_BASE}/new_site_logo_transparent.png`}
            alt="VibeCodeWeb Logo"
            width={40}
            height={40}
            className="rounded-full"
          />
          <span className="text-xl font-bold font-serif">
            VibeCode<span className="gradient-text">Web.in</span>
          </span>
        </a>

        <div className="hidden md:flex items-center gap-6">
          {navLinks.map((link) => (
            <a
              key={link.href}
              href={link.href}
              className="text-sm text-[#cbd5e1] hover:text-[#f8fafc] transition-colors"
            >
              {link.label}
            </a>
          ))}
          <a
            href="https://wa.me/919477443425"
            target="_blank"
            rel="noopener noreferrer"
            className="px-5 py-2 rounded-full bg-gradient-to-r from-[#f97316] to-[#3b82f6] text-white text-sm font-semibold hover:shadow-lg hover:shadow-[#3b82f6]/30 transition-all"
          >
            <i className="fab fa-whatsapp mr-1" /> Contact
          </a>
        </div>

        <button
          className="md:hidden text-[#f8fafc] text-xl"
          onClick={() => setMenuOpen(!menuOpen)}
          aria-label="Toggle menu"
        >
          <i className={`fas ${menuOpen ? 'fa-xmark' : 'fa-bars'}`} />
        </button>
      </div>

      {menuOpen && (
        <div className="md:hidden bg-[#0f172a]/95 backdrop-blur-md border-t border-[#334155]/30 px-6 py-4 flex flex-col gap-4">
          {navLinks.map((link) => (
            <a
              key={link.href}
              href={link.href}
              className="text-sm text-[#cbd5e1] hover:text-[#f8fafc] transition-colors"
              onClick={() => setMenuOpen(false)}
            >
              {link.label}
            </a>
          ))}
          <a
            href="https://wa.me/919477443425"
            target="_blank"
            rel="noopener noreferrer"
            className="px-4 py-2.5 rounded-full bg-gradient-to-r from-[#f97316] to-[#3b82f6] text-white text-sm font-semibold text-center"
            onClick={() => setMenuOpen(false)}
          >
            <i className="fab fa-whatsapp mr-1" /> Contact
          </a>
        </div>
      )}
    </nav>
  );
}
