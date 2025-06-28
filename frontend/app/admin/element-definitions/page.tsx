'use client';

import { useState, useEffect } from 'react';
import api from '../../../lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ElementDefinition } from '@/lib/types';
import { AxiosError } from 'axios';

export default function ElementDefinitionsPage() {
  const [elements, setElements] = useState<ElementDefinition[]>([]);
  const [currentElement, setCurrentElement] = useState<ElementDefinition | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const [formData, setFormData] = useState({
    element_type: '',
    display_name: '',
    description: '',
  });

  useEffect(() => {
    fetchElements();
  }, []);

  const fetchElements = async () => {
    try {
      setIsLoading(true);
      const response = await api.get<{ elements: ElementDefinition[] }>('/api/element-definitions');
      setElements(response.data.elements || []);
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '요소 정의를 불러오는데 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleOpenModal = (element?: ElementDefinition) => {
    if (element) {
      setCurrentElement(element);
      setFormData({
        element_type: element.element_type,
        display_name: element.display_name,
        description: element.description || '',
      });
    } else {
      setCurrentElement(null);
      setFormData({ element_type: '', display_name: '', description: '' });
    }
    setError(null);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setCurrentElement(null);
  };
  
  const validateForm = () => {
    if (!formData.element_type || !formData.display_name) {
      setError('요소 타입과 표시 이름은 필수입니다.');
      return false;
    }
    setError(null);
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateForm()) return;

    try {
      if (currentElement) {
        await api.patch(`/api/element-definitions/${currentElement.id}`, formData);
      } else {
        await api.post('/api/element-definitions', formData);
      }
      fetchElements();
      handleCloseModal();
    } catch (err) {
      const error = err as AxiosError<{ message: string }>;
      setError(error.response?.data?.message || '요청 처리 중 오류가 발생했습니다.');
    }
  };

  const handleDelete = async (id: string) => {
    if (window.confirm('정말로 이 요소를 삭제하시겠습니까?')) {
      try {
        await api.delete(`/api/element-definitions/${id}`);
        fetchElements();
      } catch (err) {
        const error = err as AxiosError<{ message: string }>;
        setError(error.response?.data?.message || '삭제 중 오류가 발생했습니다.');
      }
    }
  };
  
  if (isLoading) return <div className="p-8 text-center">요소 목록을 불러오는 중...</div>;
  if (error && !isModalOpen) return <div className="p-8 text-center text-red-500">{error}</div>;

  return (
    <div className="p-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">요소 정의 관리</h1>
        <Button onClick={() => handleOpenModal()}>새 요소 정의 추가</Button>
      </div>

      <div className="bg-white shadow rounded-lg">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>요소 타입</TableHead>
              <TableHead>표시 이름</TableHead>
              <TableHead>설명</TableHead>
              <TableHead className="text-right">작업</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {elements.length > 0 ? (
              elements.map((element) => (
                <TableRow key={element.id}>
                  <TableCell className="font-medium">{element.element_type}</TableCell>
                  <TableCell>{element.display_name}</TableCell>
                  <TableCell className="text-sm text-gray-600">{element.description}</TableCell>
                  <TableCell className="text-right">
                    <Button variant="outline" size="sm" className="mr-2" onClick={() => handleOpenModal(element)}>수정</Button>
                    <Button variant="destructive" size="sm" onClick={() => handleDelete(element.id)}>삭제</Button>
                  </TableCell>
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={4} className="text-center text-gray-500 py-8">
                  생성된 요소 정의가 없습니다.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>

      <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{currentElement ? '요소 정의 수정' : '새 요소 정의 추가'}</DialogTitle>
          </DialogHeader>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <p className="text-red-500 text-sm">{error}</p>}
            <div>
              <Label htmlFor="element_type">요소 타입 <span className="text-red-500">*</span></Label>
              <Input
                id="element_type"
                value={formData.element_type}
                onChange={(e) => setFormData({ ...formData, element_type: e.target.value })}
                disabled={!!currentElement}
                required
              />
               {currentElement && <p className="text-xs text-gray-500 mt-1">요소 타입은 변경할 수 없습니다.</p>}
            </div>
            <div>
              <Label htmlFor="display_name">표시 이름 <span className="text-red-500">*</span></Label>
              <Input
                id="display_name"
                value={formData.display_name}
                onChange={(e) => setFormData({ ...formData, display_name: e.target.value })}
                required
              />
            </div>
            <div>
              <Label htmlFor="description">설명</Label>
              <Textarea
                id="description"
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              />
            </div>
            <DialogFooter>
              <Button type="button" variant="ghost" onClick={handleCloseModal}>취소</Button>
              <Button type="submit">저장</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  );
} 