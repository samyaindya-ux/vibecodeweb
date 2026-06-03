export default function FloatingCTAs() {
  return (
    <a
      href="https://wa.me/919477443425"
      target="_blank"
      rel="noopener noreferrer"
      className="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-gradient-to-r from-[#10b981] to-[#15803d] text-white flex items-center justify-center shadow-lg shadow-[#10b981]/30 animate-bounce-soft hover:scale-110 transition-transform"
      aria-label="Chat on WhatsApp"
    >
      <i className="fab fa-whatsapp text-2xl" />
    </a>
  );
}
