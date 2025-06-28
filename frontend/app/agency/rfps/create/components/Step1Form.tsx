import { RfpFormData } from "@/lib/types";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Button } from "@/components/ui/button";
import { CalendarIcon } from "@radix-ui/react-icons";
import { format } from "date-fns";
import { Calendar } from "@/components/ui/calendar";
import { Switch } from "@/components/ui/switch";
import { Textarea } from "@/components/ui/textarea";

interface Step1FormProps {
  formData: RfpFormData;
  handleChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => void;
  handleSwitchChange: (id: string, checked: boolean) => void;
  handleDateChange: (id: string, date: Date | undefined) => void;
  handleNumericChange: (id: string, value: string) => void;
}

const formatDateForDisplay = (dateString: string) => {
  if (!dateString) return "날짜 선택";
  try {
    return format(new Date(dateString), "yyyy-MM-dd");
  } catch {
    return "날짜 선택";
  }
};

const stringToDate = (dateString: string): Date | undefined => {
  if (!dateString) return undefined;
  try {
    return new Date(dateString);
  } catch {
    return undefined;
  }
};

export default function Step1Form({ formData, handleChange, handleSwitchChange, handleDateChange, handleNumericChange }: Step1FormProps) {
  return (
    <div className="bg-white p-6 rounded-lg shadow">
      <h2 className="text-xl font-semibold mb-6">1단계: 기본 정보</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="md:col-span-2">
          <Label htmlFor="project_name">행사명 <span className="text-red-500">*</span></Label>
          <Input 
            id="project_name" 
            value={formData.project_name} 
            onChange={handleChange} 
            placeholder="예: 2024 글로벌 IT 컨퍼런스" 
            required 
          />
        </div>
        
        <div>
          <Label htmlFor="start_datetime">행사 시작일 <span className="text-red-500">*</span></Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button variant={"outline"} className={`w-full justify-start text-left font-normal ${!formData.start_datetime && "text-muted-foreground"}`}>
                <CalendarIcon className="mr-2 h-4 w-4" />
                {formatDateForDisplay(formData.start_datetime)}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
              <Calendar 
                mode="single" 
                selected={stringToDate(formData.start_datetime)} 
                onSelect={(date) => handleDateChange('start_datetime', date)} 
                initialFocus 
              />
            </PopoverContent>
          </Popover>
        </div>

        <div>
          <Label htmlFor="end_datetime">행사 마감일 <span className="text-red-500">*</span></Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button variant={"outline"} className={`w-full justify-start text-left font-normal ${!formData.end_datetime && "text-muted-foreground"}`}>
                <CalendarIcon className="mr-2 h-4 w-4" />
                {formatDateForDisplay(formData.end_datetime)}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
              <Calendar 
                mode="single" 
                selected={stringToDate(formData.end_datetime)} 
                onSelect={(date) => handleDateChange('end_datetime', date)} 
                initialFocus 
              />
            </PopoverContent>
          </Popover>
        </div>

        <div className="md:col-span-2">
          <Label htmlFor="client_name">클라이언트명 <span className="text-red-500">*</span></Label>
          <Input 
            id="client_name" 
            value={formData.client_name} 
            onChange={handleChange} 
            placeholder="예: ABC 회사" 
            required 
          />
        </div>

        <div>
          <Label htmlFor="client_contact_person">담당자명 <span className="text-red-500">*</span></Label>
          <Input 
            id="client_contact_person" 
            value={formData.client_contact_person} 
            onChange={handleChange} 
            placeholder="예: 김담당자" 
            required 
          />
        </div>

        <div>
          <Label htmlFor="client_contact_number">담당자 연락처 <span className="text-red-500">*</span></Label>
          <Input 
            id="client_contact_number" 
            value={formData.client_contact_number} 
            onChange={handleChange} 
            placeholder="예: 010-1234-5678" 
            required 
          />
        </div>
        
        <div className="md:col-span-2">
          <Label htmlFor="location">행사 장소 <span className="text-red-500">*</span></Label>
          <Input 
            id="location" 
            value={formData.location} 
            onChange={handleChange} 
            placeholder="예: 서울 코엑스 컨벤션센터 홀 A" 
            required 
          />
        </div>

        <div className="flex items-center space-x-2">
          <Switch 
            id="is_indoor" 
            checked={formData.is_indoor} 
            onCheckedChange={(checked) => handleSwitchChange('is_indoor', checked)} 
          />
          <Label htmlFor="is_indoor">실내 행사</Label>
        </div>

        <div>
          <Label htmlFor="budget_including_vat">총 예산 (VAT 포함) <span className="text-red-500">*</span></Label>
          <Input 
            id="budget_including_vat" 
            type="number" 
            value={formData.budget_including_vat === 0 ? '' : formData.budget_including_vat} 
            onChange={(e) => handleNumericChange('budget_including_vat', e.target.value)} 
            placeholder="예: 50000000" 
            required 
          />
        </div>

        <div className="md:col-span-2">
          <Label htmlFor="rfp_description">RFP 설명 <span className="text-red-500">*</span></Label>
          <Textarea 
            id="rfp_description" 
            value={formData.rfp_description} 
            onChange={handleChange} 
            placeholder="행사에 대한 자세한 설명을 입력해주세요..." 
            rows={4} 
            required 
          />
        </div>

        <div>
          <Label htmlFor="closing_at">공고 마감일 <span className="text-red-500">*</span></Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button variant={"outline"} className={`w-full justify-start text-left font-normal ${!formData.closing_at && "text-muted-foreground"}`}>
                <CalendarIcon className="mr-2 h-4 w-4" />
                {formatDateForDisplay(formData.closing_at)}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
              <Calendar 
                mode="single" 
                selected={stringToDate(formData.closing_at)} 
                onSelect={(date) => handleDateChange('closing_at', date)} 
                initialFocus 
              />
            </PopoverContent>
          </Popover>
        </div>
      </div>
    </div>
  );
}