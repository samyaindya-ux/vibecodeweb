const tools = [
  'ChatGPT',
  'Claude',
  'Gemini',
  'Midjourney',
  'GitHub Copilot',
  'Perplexity',
  'Pomelli by Google Labs',
  'Stitch',
  'NotebookLM by Google Labs',
  'Llama 3',
];

function ToolList() {
  return (
    <>
      {tools.map((tool, i) => (
        <span
          key={i}
          className="inline-flex items-center gap-3 text-[#cbd5e1] text-sm font-medium px-8 shrink-0"
        >
          <i className="fas fa-microchip text-[#3b82f6] text-xs" />
          {tool}
        </span>
      ))}
    </>
  );
}

export default function MarqueeBanner() {
  return (
    <div className="py-6 bg-[#0f172a]/50 border-y border-[#334155]/30 overflow-hidden">
      <p className="text-center text-xs text-[#cbd5e1]/60 uppercase tracking-widest mb-3">
        Powered by Industry-Leading AI: Expertise in
      </p>
      <div className="flex animate-marquee">
        <ToolList />
        <ToolList />
      </div>
    </div>
  );
}
