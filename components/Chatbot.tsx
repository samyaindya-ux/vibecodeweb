'use client';
import { useState } from 'react';

const serviceOptions = [
  'Retail & POS',
  'AI Automations',
  'Doc Analyzer',
  'SaaS',
  'Content Gen',
  'AI Strategy',
];

type Message = { from: 'bot' | 'user'; text: string };

export default function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([
    {
      from: 'bot',
      text: "Namaste! 🙏 I'm the VibeCodeWeb AI assistant. Please select a service you're interested in:",
    },
  ]);
  const [selectedService, setSelectedService] = useState<string | null>(null);

  const handleSelect = (svc: string) => {
    setSelectedService(svc);
    setMessages((prev) => [
      ...prev,
      { from: 'user', text: svc },
      {
        from: 'bot',
        text: `Great choice! Click below to chat with us directly about our ${svc} service on WhatsApp.`,
      },
    ]);
  };

  return (
    <>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="fixed bottom-6 left-6 z-50 w-14 h-14 rounded-full bg-gradient-to-r from-[#3b82f6] to-[#8b5cf6] text-white flex items-center justify-center shadow-lg hover:shadow-[#3b82f6]/40 hover:scale-105 transition-all"
        aria-label="Toggle chatbot"
      >
        {isOpen ? (
          <i className="fas fa-xmark text-xl" />
        ) : (
          <>
            <i className="fas fa-message text-xl" />
            <span className="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full animate-ping" />
            <span className="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full" />
          </>
        )}
      </button>

      {isOpen && (
        <div className="fixed bottom-24 left-6 z-50 w-80 glass-card overflow-hidden shadow-2xl shadow-[#3b82f6]/20">
          {/* Header */}
          <div className="bg-gradient-to-r from-[#3b82f6] to-[#8b5cf6] p-4 flex items-center gap-3">
            <div className="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
              <i className="fas fa-robot text-white text-lg" />
            </div>
            <div>
              <p className="text-white font-semibold text-sm">VibeCodeWeb AI</p>
              <div className="flex items-center gap-1.5">
                <span className="w-2 h-2 bg-[#10b981] rounded-full animate-ping" />
                <span className="text-white/70 text-xs">Online</span>
              </div>
            </div>
          </div>

          {/* Messages */}
          <div className="p-4 max-h-64 overflow-y-auto space-y-3">
            {messages.map((msg, i) => (
              <div
                key={i}
                className={`flex ${msg.from === 'user' ? 'justify-end' : 'justify-start'}`}
              >
                <div
                  className={`max-w-[80%] px-4 py-2.5 rounded-2xl text-sm ${
                    msg.from === 'user'
                      ? 'bg-[#3b82f6] text-white rounded-tr-sm'
                      : 'bg-[#0f172a]/80 text-[#cbd5e1] rounded-tl-sm'
                  }`}
                >
                  {msg.text}
                </div>
              </div>
            ))}

            {!selectedService && (
              <div className="grid grid-cols-2 gap-2 mt-2">
                {serviceOptions.map((svc) => (
                  <button
                    key={svc}
                    onClick={() => handleSelect(svc)}
                    className="px-3 py-2 rounded-lg bg-[#3b82f6]/20 border border-[#3b82f6]/30 text-[#3b82f6] text-xs font-medium hover:bg-[#3b82f6]/30 transition-colors text-left"
                  >
                    {svc}
                  </button>
                ))}
              </div>
            )}

            {selectedService && (
              <a
                href={`https://wa.me/919477443425?text=Hi%2C%20I%20am%20interested%20in%20your%20${encodeURIComponent(selectedService)}%20service.`}
                target="_blank"
                rel="noopener noreferrer"
                className="block w-full py-3 rounded-xl bg-gradient-to-r from-[#10b981] to-[#3b82f6] text-white text-sm font-semibold text-center hover:shadow-lg transition-all mt-2"
              >
                <i className="fab fa-whatsapp mr-2" />
                Chat on WhatsApp
              </a>
            )}
          </div>
        </div>
      )}
    </>
  );
}
