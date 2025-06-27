'use client';

import { useState, useEffect } from 'react';
import api from '../../../lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface ElementDefinition {
  id: string;
  element_type: string;
  display_name: string;
  description: string;
  input_schema?: any;
  default_details_template?: any;
  recommended_elements?: any;
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
    display_name: '',
    description: '',
  });
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // 요소 정의 목록 조회
  const fetchElements = async () => {
    try {
      setIsLoading(true);
      console.log('API 호출 시작...');
      const response = await api.get('/api/element-definitions');
      console.log('API 응답:', response.data);
      
      // 백엔드에서 response.data.elements로 응답함
      const elementsData = response.data.elements || response.data;
      console.log('요소 데이터:', elementsData);
      setElements(elementsData);
    } catch (err: any) {
      console.error('요소 정의 조회 실패:', err);
      setError('요소 정의를 불러오는데 실패했습니다: ' + (err.response?.data?.message || err.message));
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchElements();
  }, []);

  const handleOpenModal = (element?: ElementDefinition) => {
    console.log('수정 버튼 클릭됨, 요소:', element);
    
    if (element) {
      setCurrentElement(element);
      setFormData({
        element_type: element.element_type,
        display_name: element.display_name,
        description: element.description || '',
      });
      console.log('수정 모드 - 폼 데이터 설정됨:', {
        element_type: element.element_type,
        display_name: element.display_name,
        description: element.description || '',
      });
    } else {
      setCurrentElement(null);
      setFormData({ element_type: '', display_name: '', description: '' });
      console.log('추가 모드 - 폼 데이터 초기화됨');
    }
    setError(null);
    setIsModalOpen(true);
    console.log('모달 열기 설정됨');
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setCurrentElement(null);
    setFormData({ element_type: '', display_name: '', description: '' });
    setError(null);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { id, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [id]: value,
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setIsSubmitting(true);

    try {
      const { element_type, display_name, description } = formData;
      
      if (!element_type.trim() || !display_name.trim()) {
        setError('요소 타입과 표시 이름을 모두 입력해주세요.');
        return;
      }

      if (currentElement) {
        await api.put(`/api/element-definitions/${currentElement.id}`, {
          display_name,
          description,
        });
      } else {
        await api.post('/api/element-definitions', {
          element_type,
          display_name,
          description,
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
                <TableHead>표시 이름</TableHead>
                <TableHead>설명</TableHead>
                <TableHead>생성일</TableHead>
                <TableHead className="text-right">액션</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {elements.map((element) => (
                <TableRow key={element.id}>
                  <TableCell className="font-medium">{element.element_type}</TableCell>
                  <TableCell>{element.display_name}</TableCell>
                  <TableCell>{element.description}</TableCell>
                  <TableCell>{new Date(element.created_at).toLocaleDateString()}</TableCell>
                  <TableCell className="text-right">
                    <Button
                      variant="outline"
                      size="sm"
                      className="mr-2"
                      onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('수정 버튼 클릭 이벤트 발생, 요소 ID:', element.id);
                        handleOpenModal(element);
                      }}
                    >
                      수정
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('삭제 버튼 클릭 이벤트 발생, 요소 ID:', element.id);
                        handleDelete(element.id);
                      }}
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
                  placeholder="예: stage, sound, lighting"
                  disabled={!!currentElement} // 수정 시에는 element_type 변경 불가
                  required
                />
              </div>

              <div className="grid grid-cols-4 items-center gap-4">
                <Label htmlFor="display_name" className="text-right">
                  표시 이름
                </Label>
                <Input
                  id="display_name"
                  value={formData.display_name}
                  onChange={handleChange}
                  className="col-span-3"
                  placeholder="예: 무대, 음향, 조명"
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
                />
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