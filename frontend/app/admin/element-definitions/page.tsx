'use client';

import { useState, useEffect } from 'react';
import api from '../../../lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';

interface ElementDefinition {
  id: string;
  element_type: string;
  description: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export default function ElementDefinitionsPage() {
  const [elements, setElements] = useState<ElementDefinition[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentElement, setCurrentElement] = useState<ElementDefinition | null>(null);
  const [formData, setFormData] = useState({
    element_type: '',
    description: '',
    is_active: true,
  });
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // 요소 정의 목록 조회
  const fetchElements = async () => {
    try {
      setIsLoading(true);
      const response = await api.get('/api/element-definitions');
      setElements(response.data);
    } catch (err: any) {
      console.error('요소 정의 조회 실패:', err);
      setError('요소 정의를 불러오는데 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchElements();
  }, []);

  const handleOpenModal = (element?: ElementDefinition) => {
    if (element) {
      setCurrentElement(element);
      setFormData({
        element_type: element.element_type,
        description: element.description,
        is_active: element.is_active,
      });
    } else {
      setCurrentElement(null);
      setFormData({ element_type: '', description: '', is_active: true });
    }
    setError(null);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setCurrentElement(null);
    setFormData({ element_type: '', description: '', is_active: true });
    setError(null);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { id, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [id]: value,
    }));
  };

  const handleSwitchChange = (checked: boolean) => {
    setFormData((prev) => ({
      ...prev,
      is_active: checked,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsSubmitting(true);

    try {
      const { element_type, description, is_active } = formData;
      
      if (!element_type.trim() || !description.trim()) {
        setError('요소 타입과 설명을 모두 입력해주세요.');
        return;
      }

      if (currentElement) {
        await api.put(`/api/element-definitions/${currentElement.id}`, {
          element_type,
          description,
          is_active,
        });
      } else {
        await api.post('/api/element-definitions', {
          element_type,
          description,
          is_active,
        });
      }

      await fetchElements(); // 목록 새로고침
      handleCloseModal();
    } catch (err: any) {
      console.error('요소 저장 실패:', err);
      setError(err.response?.data?.message || '요소 저장에 실패했습니다.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!window.confirm('정말로 이 요소 정의를 삭제하시겠습니까?')) {
      return;
    }

    try {
      await api.delete(`/api/element-definitions/${id}`);
      await fetchElements(); // 목록 새로고침
    } catch (err: any) {
      console.error('요소 삭제 실패:', err);
      setError(err.response?.data?.message || '요소 삭제에 실패했습니다.');
    }
  };

  if (isLoading) {
    return (
      <div className="text-center text-gray-500 py-8">
        요소 정의 목록을 불러오는 중...
      </div>
    );
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-800">요소 정의 관리</h1>
        <Button onClick={() => handleOpenModal()}>새 요소 정의 추가</Button>
      </div>

      {error && !isModalOpen && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      <div className="bg-white rounded-lg shadow-md">
        {elements && elements.length > 0 ? (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>요소 타입</TableHead>
                <TableHead>설명</TableHead>
                <TableHead>활성화</TableHead>
                <TableHead>생성일</TableHead>
                <TableHead className="text-right">액션</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {elements.map((element) => (
                <TableRow key={element.id}>
                  <TableCell className="font-medium">{element.element_type}</TableCell>
                  <TableCell>{element.description}</TableCell>
                  <TableCell>
                    <span className={`px-2 py-1 rounded-full text-xs ${
                      element.is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'
                    }`}>
                      {element.is_active ? '활성' : '비활성'}
                    </span>
                  </TableCell>
                  <TableCell>{new Date(element.created_at).toLocaleDateString()}</TableCell>
                  <TableCell className="text-right">
                    <Button
                      variant="outline"
                      size="sm"
                      className="mr-2"
                      onClick={() => handleOpenModal(element)}
                    >
                      수정
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => handleDelete(element.id)}
                    >
                      삭제
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        ) : (
          <div className="text-center text-gray-500 py-8">
            정의된 요소가 없습니다.
          </div>
        )}
      </div>

      <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
        <DialogContent className="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>
              {currentElement ? '요소 정의 수정' : '새 요소 정의 추가'}
            </DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit}>
            <div className="grid gap-4 py-4">
              {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm">
                  {error}
                </div>
              )}
              
              <div className="grid grid-cols-4 items-center gap-4">
                <Label htmlFor="element_type" className="text-right">
                  요소 타입
                </Label>
                <Input
                  id="element_type"
                  value={formData.element_type}
                  onChange={handleChange}
                  className="col-span-3"
                  placeholder="예: 무대설치, 음향장비, 조명장비"
                  required
                />
              </div>
              
              <div className="grid grid-cols-4 items-start gap-4">
                <Label htmlFor="description" className="text-right pt-2">
                  설명
                </Label>
                <Textarea
                  id="description"
                  value={formData.description}
                  onChange={handleChange}
                  className="col-span-3"
                  placeholder="요소에 대한 상세 설명을 입력하세요"
                  rows={3}
                  required
                />
              </div>
              
              <div className="grid grid-cols-4 items-center gap-4">
                <Label htmlFor="is_active" className="text-right">
                  활성화
                </Label>
                <div className="col-span-3 flex items-center space-x-2">
                  <Switch
                    id="is_active"
                    checked={formData.is_active}
                    onCheckedChange={handleSwitchChange}
                  />
                  <span className="text-sm text-gray-600">
                    {formData.is_active ? '활성' : '비활성'}
                  </span>
                </div>
              </div>
            </div>
            
            <DialogFooter>
              <Button 
                type="button" 
                variant="outline" 
                onClick={handleCloseModal}
                disabled={isSubmitting}
              >
                취소
              </Button>
              <Button type="submit" disabled={isSubmitting}>
                {isSubmitting ? '저장 중...' : '저장'}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  );
} 