import Navbar from '@/components/Navbar';
import Hero from '@/components/Hero';
import MarqueeBanner from '@/components/MarqueeBanner';
import AIBenefits from '@/components/AIBenefits';
import AIAdvantages from '@/components/AIAdvantages';
import Vision from '@/components/Vision';
import Mission from '@/components/Mission';
import Services from '@/components/Services';
import Pricing from '@/components/Pricing';
import Contact from '@/components/Contact';
import Footer from '@/components/Footer';
import FloatingCTAs from '@/components/FloatingCTAs';
import Chatbot from '@/components/Chatbot';

export default function Home() {
  return (
    <>
      <Navbar />
      <main>
        <Hero />
        <MarqueeBanner />
        <AIBenefits />
        <AIAdvantages />
        <Vision />
        <Mission />
        <Services />
        <Pricing />
        <Contact />
      </main>
      <Footer />
      <FloatingCTAs />
      <Chatbot />
    </>
  );
}
