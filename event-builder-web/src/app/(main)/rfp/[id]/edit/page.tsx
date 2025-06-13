import { cookies } from 'next/headers';
import { notFound } from 'next/navigation';
import { fetchRfpDetails, Rfp } from '@/lib/api';
import RfpForm from '@/components/rfp/RfpForm';

interface RfpEditPageProps {
  params: Promise<{
    id: string;
  }>;
}

export default async function RfpEditPage({ params }: RfpEditPageProps) {
  const cookieStore = await cookies();
  const token = cookieStore.get('auth-token')?.value || null;
  const { id } = await params;
  let rfp: Rfp;

  try {
    const response = await fetchRfpDetails(id, token);
    if (!response.success) {
      notFound();
    }
    rfp = response.data;
  } catch (error) {
    console.error(`Error fetching RFP for edit (ID: ${id}):`, error);
    notFound();
  }

  return <RfpForm initialData={rfp} />;
}