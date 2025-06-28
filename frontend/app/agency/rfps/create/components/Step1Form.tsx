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

const formatDateForDisplay = (date: Date | null) => {
  return date ? format(date, "yyyy-MM-dd") : "날짜 선택";
};

export default function Step1Form({ formData, handleChange, handleSwitchChange, handleDateChange, handleNumericChange }: Step1FormProps) {
  return (
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
              selected={formData.start_datetime || undefined} 
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
              selected={formData.end_datetime || undefined} 
              onSelect={(date) => handleDateChange('end_datetime', date)} 
              initialFocus 
            />
          </PopoverContent>
        </Popover>
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
        <div className="space-y-2">
          <Input 
            id="budget_including_vat" 
            type="number" 
            value={formData.budget_including_vat === null ? '' : formData.budget_including_vat} 
            onChange={(e) => handleNumericChange('budget_including_vat', e.target.value)} 
            placeholder="예: 50000000" 
            required 
          />
          <div className="flex items-center space-x-2">
            <Switch 
              id="is_budget_public" 
              checked={formData.is_budget_public} 
              onCheckedChange={(checked) => handleSwitchChange('is_budget_public', checked)} 
            />
            <Label htmlFor="is_budget_public" className="text-sm">예산 공개</Label>
          </div>
        </div>
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
              selected={formData.closing_at || undefined} 
              onSelect={(date) => handleDateChange('closing_at', date)} 
              initialFocus 
            />
          </PopoverContent>
        </Popover>
      </div>
    </div>
  );
}