'use client';

import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import { 
  Building2, 
  Users, 
  Calendar, 
  Edit, 
  Save, 
  X, 
  AlertCircle, 
  CheckCircle,
  XCircle,
  Clock,
  Shield,
  Trash2,
  ChevronDown,
  ChevronRight,
  UserCheck,
  UserX,
  Plus,
  Search,
  Filter
} from 'lucide-react';
import api from '@/lib/api';

interface User {
  id: string;
  name: string;
  email: string;
  position?: string;
  job_title?: string;
  account_status: 'pending' | 'approved' | 'rejected' | 'suspended';
  created_at: string;
  joined_at?: string;
}

interface Agency {
  id: string;
  name: string;
  business_registration_number: string;
  address: string;
  subscription_status: 'active' | 'inactive' | 'suspended';
  subscription_end_date: string | null;
  master_user_id: string;
  masterUser: User;
  members: { user: User }[];
  member_count: number;
  created_at: string;
}

interface Vendor {
  id: string;
  name: string;
  business_registration_number: string;
  address: string;
  description?: string;
  specialties?: string[];
  status: 'active' | 'inactive' | 'banned';
  ban_reason?: string;
  banned_at?: string;
  master_user_id: string;
  masterUser: User;
  members: { user: User }[];
  member_count: number;
  created_at: string;
}

interface EditData {
  subscription_end_date?: string;
  subscription_status?: string;
  status?: string;
  ban_reason?: string;
  name?: string;
  address?: string;
}

type TabType = 'agencies' | 'vendors';

export default function UsersManagement() {
  const [activeTab, setActiveTab] = useState<TabType>('agencies');
  const [agencies, setAgencies] = useState<Agency[]>([]);
  const [vendors, setVendors] = useState<Vendor[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Expansion states for company hierarchy
  const [expandedAgencies, setExpandedAgencies] = useState<Set<string>>(new Set());
  const [expandedVendors, setExpandedVendors] = useState<Set<string>>(new Set());
  
  // Edit modal states
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [editingEntity, setEditingEntity] = useState<Agency | Vendor | null>(null);
  const [editingType, setEditingType] = useState<'agency' | 'vendor' | null>(null);
  const [editData, setEditData] = useState<EditData>({});
  const [isSaving, setIsSaving] = useState(false);

  // User status edit states
  const [isUserModalOpen, setIsUserModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [userStatusData, setUserStatusData] = useState({
    account_status: '',
    admin_notes: ''
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      const [agenciesResponse, vendorsResponse] = await Promise.all([
        api.get('/api/admin/agencies'),
        api.get('/api/admin/vendors')
      ]);

      if (agenciesResponse.data.success) {
        setAgencies(agenciesResponse.data.data);
      }
      
      if (vendorsResponse.data.success) {
        setVendors(vendorsResponse.data.data);
      }
    } catch (err: any) {
      console.error('데이터 로드 실패:', err);
      setError('데이터를 불러오는데 실패했습니다.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleEditEntity = (entity: Agency | Vendor, type: 'agency' | 'vendor') => {
    setEditingEntity(entity);
    setEditingType(type);
    
    if (type === 'agency') {
      const agency = entity as Agency;
      setEditData({
        subscription_end_date: agency.subscription_end_date ? 
          new Date(agency.subscription_end_date).toISOString().split('T')[0] : '',
        subscription_status: agency.subscription_status,
        name: agency.name,
        address: agency.address
      });
    } else {
      const vendor = entity as Vendor;
      setEditData({
        status: vendor.status,
        ban_reason: vendor.ban_reason || '',
        name: vendor.name,
        address: vendor.address
      });
    }
    
    setIsEditModalOpen(true);
  };

  const handleSaveEntity = async () => {
    if (!editingEntity || !editingType) return;

    setIsSaving(true);
    setError(null);

    try {
      const endpoint = editingType === 'agency' 
        ? `/api/admin/agencies/${editingEntity.id}`
        : `/api/admin/vendors/${editingEntity.id}`;

      const response = await api.put(endpoint, editData);

      if (response.data.success) {
        // Update local state
        if (editingType === 'agency') {
          setAgencies(prev => prev.map(agency => 
            agency.id === editingEntity.id ? { ...agency, ...editData } : agency
          ));
        } else {
          setVendors(prev => prev.map(vendor => 
            vendor.id === editingEntity.id ? { ...vendor, ...editData } : vendor
          ));
        }
        
        setIsEditModalOpen(false);
        setEditingEntity(null);
        setEditingType(null);
        setEditData({});
      } else {
        setError(response.data.message || '저장에 실패했습니다.');
      }
    } catch (err: any) {
      console.error('저장 실패:', err);
      setError(err.response?.data?.message || '저장에 실패했습니다.');
    } finally {
      setIsSaving(false);
    }
  };

  const handleEditUser = (user: User) => {
    setEditingUser(user);
    setUserStatusData({
      account_status: user.account_status,
      admin_notes: ''
    });
    setIsUserModalOpen(true);
  };

  const handleSaveUserStatus = async () => {
    if (!editingUser) return;

    setIsSaving(true);
    setError(null);

    try {
      const response = await api.put(`/api/admin/users/${editingUser.id}/status`, userStatusData);

      if (response.data.success) {
        // Reload data to get updated user information
        await loadData();
        setIsUserModalOpen(false);
        setEditingUser(null);
        setUserStatusData({ account_status: '', admin_notes: '' });
      } else {
        setError(response.data.message || '사용자 상태 업데이트에 실패했습니다.');
      }
    } catch (err: any) {
      console.error('사용자 상태 업데이트 실패:', err);
      setError(err.response?.data?.message || '사용자 상태 업데이트에 실패했습니다.');
    } finally {
      setIsSaving(false);
    }
  };

  const toggleEntityExpansion = (entityId: string, type: 'agency' | 'vendor') => {
    if (type === 'agency') {
      setExpandedAgencies(prev => {
        const newSet = new Set(prev);
        if (newSet.has(entityId)) {
          newSet.delete(entityId);
        } else {
          newSet.add(entityId);
        }
        return newSet;
      });
    } else {
      setExpandedVendors(prev => {
        const newSet = new Set(prev);
        if (newSet.has(entityId)) {
          newSet.delete(entityId);
        } else {
          newSet.add(entityId);
        }
        return newSet;
      });
    }
  };

  const getStatusBadgeVariant = (status: string) => {
    switch (status) {
      case 'active':
      case 'approved':
        return 'default';
      case 'inactive':
      case 'pending':
        return 'secondary';
      case 'suspended':
      case 'rejected':
        return 'destructive';
      case 'banned':
        return 'destructive';
      default:
        return 'outline';
    }
  };

  const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
      'active': '활성',
      'inactive': '비활성',
      'suspended': '정지',
      'banned': '차단',
      'pending': '승인대기',
      'approved': '승인완료',
      'rejected': '승인거절'
    };
    return statusMap[status] || status;
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('ko-KR');
  };

  const filteredAgencies = agencies.filter(agency =>
    agency.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    agency.business_registration_number.includes(searchTerm) ||
    agency.masterUser.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const filteredVendors = vendors.filter(vendor =>
    vendor.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    vendor.business_registration_number.includes(searchTerm) ||
    vendor.masterUser.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-6">
        <div className="flex items-center justify-center h-64">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p className="text-gray-600">데이터를 불러오는 중...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-6 space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">사용자 관리</h1>
          <p className="text-gray-600 mt-1">대행사와 용역사 회원을 관리합니다</p>
        </div>
        <div className="flex items-center gap-2">
          <Users className="w-8 h-8 text-blue-600" />
          <Badge variant="outline" className="text-blue-600">
            Admin Panel
          </Badge>
        </div>
      </div>

      {/* Error Display */}
      {error && (
        <div className="p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-red-600" />
          <span className="text-red-800 text-sm">{error}</span>
          <Button 
            variant="ghost" 
            size="sm" 
            onClick={() => setError(null)}
            className="ml-auto"
          >
            <X className="w-4 h-4" />
          </Button>
        </div>
      )}

      {/* Search and Tabs */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div className="flex space-x-1 bg-gray-100 p-1 rounded-lg">
              <button
                onClick={() => setActiveTab('agencies')}
                className={`flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                  activeTab === 'agencies'
                    ? 'bg-white text-blue-600 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                }`}
              >
                <Building2 className="w-4 h-4" />
                대행사 ({agencies.length})
              </button>
              <button
                onClick={() => setActiveTab('vendors')}
                className={`flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                  activeTab === 'vendors'
                    ? 'bg-white text-blue-600 shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                }`}
              >
                <Shield className="w-4 h-4" />
                용역사 ({vendors.length})
              </button>
            </div>
            
            <div className="flex items-center gap-2">
              <div className="relative">
                <Search className="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                <Input
                  placeholder="회사명, 사업자번호, 담당자명으로 검색..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10 w-80"
                />
              </div>
            </div>
          </div>
          
          <CardDescription>
            {activeTab === 'agencies' 
              ? '대행사 회사와 소속 사용자들을 관리합니다' 
              : '용역사 회사와 소속 사용자들을 관리합니다'
            }
          </CardDescription>
        </CardHeader>

        <CardContent>
          {/* Agencies Tab */}
          {activeTab === 'agencies' && (
            <div className="space-y-4">
              {filteredAgencies.map((agency) => (
                <Card key={agency.id} className="border-l-4 border-l-blue-500">
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <button
                          onClick={() => toggleEntityExpansion(agency.id, 'agency')}
                          className="p-1 hover:bg-gray-100 rounded"
                        >
                          {expandedAgencies.has(agency.id) ? (
                            <ChevronDown className="w-4 h-4" />
                          ) : (
                            <ChevronRight className="w-4 h-4" />
                          )}
                        </button>
                        <Building2 className="w-5 h-5 text-blue-600" />
                        <div>
                          <h3 className="font-semibold text-lg">{agency.name}</h3>
                          <p className="text-sm text-gray-600">
                            사업자번호: {agency.business_registration_number} | 
                            멤버 {agency.member_count}명 | 
                            설립일: {formatDate(agency.created_at)}
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Badge variant={getStatusBadgeVariant(agency.subscription_status)}>
                          {getStatusText(agency.subscription_status)}
                        </Badge>
                        {agency.subscription_end_date && (
                          <div className="text-sm text-gray-600 flex items-center gap-1">
                            <Calendar className="w-4 h-4" />
                            만료: {formatDate(agency.subscription_end_date)}
                          </div>
                        )}
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleEditEntity(agency, 'agency')}
                        >
                          <Edit className="w-4 h-4 mr-1" />
                          편집
                        </Button>
                      </div>
                    </div>
                  </CardHeader>

                  {expandedAgencies.has(agency.id) && (
                    <CardContent className="pt-0">
                      <div className="space-y-4">
                        {/* Company Info */}
                        <div className="bg-gray-50 p-4 rounded-lg">
                          <h4 className="font-medium mb-2">회사 정보</h4>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                              <span className="text-gray-600">주소:</span>
                              <p>{agency.address}</p>
                            </div>
                            <div>
                              <span className="text-gray-600">대표 사용자:</span>
                              <p>{agency.masterUser.name} ({agency.masterUser.email})</p>
                            </div>
                          </div>
                        </div>

                        {/* Members */}
                        <div>
                          <h4 className="font-medium mb-3 flex items-center gap-2">
                            <Users className="w-4 h-4" />
                            소속 멤버 ({agency.member_count}명)
                          </h4>
                          <div className="border rounded-lg">
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>이름</TableHead>
                                  <TableHead>이메일</TableHead>
                                  <TableHead>직급/직책</TableHead>
                                  <TableHead>가입일</TableHead>
                                  <TableHead>상태</TableHead>
                                  <TableHead>작업</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {/* Master User */}
                                <TableRow className="bg-blue-50">
                                  <TableCell className="font-medium">
                                    {agency.masterUser.name}
                                    <Badge variant="outline" className="ml-2 text-xs">대표</Badge>
                                  </TableCell>
                                  <TableCell>{agency.masterUser.email}</TableCell>
                                  <TableCell>
                                    {agency.masterUser.position && agency.masterUser.job_title
                                      ? `${agency.masterUser.position} / ${agency.masterUser.job_title}`
                                      : agency.masterUser.position || agency.masterUser.job_title || '-'
                                    }
                                  </TableCell>
                                  <TableCell>{formatDate(agency.masterUser.created_at)}</TableCell>
                                  <TableCell>
                                    <Badge variant={getStatusBadgeVariant(agency.masterUser.account_status)}>
                                      {getStatusText(agency.masterUser.account_status)}
                                    </Badge>
                                  </TableCell>
                                  <TableCell>
                                    <Button
                                      variant="ghost"
                                      size="sm"
                                      onClick={() => handleEditUser(agency.masterUser)}
                                    >
                                      <UserCheck className="w-4 h-4" />
                                    </Button>
                                  </TableCell>
                                </TableRow>
                                {/* Regular Members */}
                                {agency.members.map((member) => (
                                  <TableRow key={member.user.id}>
                                    <TableCell>{member.user.name}</TableCell>
                                    <TableCell>{member.user.email}</TableCell>
                                    <TableCell>
                                      {member.user.position && member.user.job_title
                                        ? `${member.user.position} / ${member.user.job_title}`
                                        : member.user.position || member.user.job_title || '-'
                                      }
                                    </TableCell>
                                    <TableCell>{formatDate(member.user.joined_at || member.user.created_at)}</TableCell>
                                    <TableCell>
                                      <Badge variant={getStatusBadgeVariant(member.user.account_status)}>
                                        {getStatusText(member.user.account_status)}
                                      </Badge>
                                    </TableCell>
                                    <TableCell>
                                      <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleEditUser(member.user)}
                                      >
                                        <UserCheck className="w-4 h-4" />
                                      </Button>
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </div>
                        </div>
                      </div>
                    </CardContent>
                  )}
                </Card>
              ))}
            </div>
          )}

          {/* Vendors Tab */}
          {activeTab === 'vendors' && (
            <div className="space-y-4">
              {filteredVendors.map((vendor) => (
                <Card key={vendor.id} className="border-l-4 border-l-green-500">
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <button
                          onClick={() => toggleEntityExpansion(vendor.id, 'vendor')}
                          className="p-1 hover:bg-gray-100 rounded"
                        >
                          {expandedVendors.has(vendor.id) ? (
                            <ChevronDown className="w-4 h-4" />
                          ) : (
                            <ChevronRight className="w-4 h-4" />
                          )}
                        </button>
                        <Shield className="w-5 h-5 text-green-600" />
                        <div>
                          <h3 className="font-semibold text-lg">{vendor.name}</h3>
                          <p className="text-sm text-gray-600">
                            사업자번호: {vendor.business_registration_number} | 
                            멤버 {vendor.member_count}명 | 
                            설립일: {formatDate(vendor.created_at)}
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Badge variant={getStatusBadgeVariant(vendor.status)}>
                          {getStatusText(vendor.status)}
                        </Badge>
                        {vendor.banned_at && (
                          <div className="text-sm text-red-600 flex items-center gap-1">
                            <XCircle className="w-4 h-4" />
                            차단: {formatDate(vendor.banned_at)}
                          </div>
                        )}
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleEditEntity(vendor, 'vendor')}
                        >
                          <Edit className="w-4 h-4 mr-1" />
                          편집
                        </Button>
                      </div>
                    </div>
                  </CardHeader>

                  {expandedVendors.has(vendor.id) && (
                    <CardContent className="pt-0">
                      <div className="space-y-4">
                        {/* Company Info */}
                        <div className="bg-gray-50 p-4 rounded-lg">
                          <h4 className="font-medium mb-2">회사 정보</h4>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                              <span className="text-gray-600">주소:</span>
                              <p>{vendor.address}</p>
                            </div>
                            <div>
                              <span className="text-gray-600">대표 사용자:</span>
                              <p>{vendor.masterUser.name} ({vendor.masterUser.email})</p>
                            </div>
                            {vendor.description && (
                              <div className="md:col-span-2">
                                <span className="text-gray-600">회사 설명:</span>
                                <p>{vendor.description}</p>
                              </div>
                            )}
                            {vendor.specialties && vendor.specialties.length > 0 && (
                              <div className="md:col-span-2">
                                <span className="text-gray-600">전문 분야:</span>
                                <div className="flex flex-wrap gap-1 mt-1">
                                  {vendor.specialties.map((specialty, index) => (
                                    <Badge key={index} variant="secondary" className="text-xs">
                                      {specialty}
                                    </Badge>
                                  ))}
                                </div>
                              </div>
                            )}
                            {vendor.ban_reason && (
                              <div className="md:col-span-2">
                                <span className="text-red-600">차단 사유:</span>
                                <p className="text-red-800">{vendor.ban_reason}</p>
                              </div>
                            )}
                          </div>
                        </div>

                        {/* Members */}
                        <div>
                          <h4 className="font-medium mb-3 flex items-center gap-2">
                            <Users className="w-4 h-4" />
                            소속 멤버 ({vendor.member_count}명)
                          </h4>
                          <div className="border rounded-lg">
                            <Table>
                              <TableHeader>
                                <TableRow>
                                  <TableHead>이름</TableHead>
                                  <TableHead>이메일</TableHead>
                                  <TableHead>직급/직책</TableHead>
                                  <TableHead>가입일</TableHead>
                                  <TableHead>상태</TableHead>
                                  <TableHead>작업</TableHead>
                                </TableRow>
                              </TableHeader>
                              <TableBody>
                                {/* Master User */}
                                <TableRow className="bg-green-50">
                                  <TableCell className="font-medium">
                                    {vendor.masterUser.name}
                                    <Badge variant="outline" className="ml-2 text-xs">대표</Badge>
                                  </TableCell>
                                  <TableCell>{vendor.masterUser.email}</TableCell>
                                  <TableCell>
                                    {vendor.masterUser.position && vendor.masterUser.job_title
                                      ? `${vendor.masterUser.position} / ${vendor.masterUser.job_title}`
                                      : vendor.masterUser.position || vendor.masterUser.job_title || '-'
                                    }
                                  </TableCell>
                                  <TableCell>{formatDate(vendor.masterUser.created_at)}</TableCell>
                                  <TableCell>
                                    <Badge variant={getStatusBadgeVariant(vendor.masterUser.account_status)}>
                                      {getStatusText(vendor.masterUser.account_status)}
                                    </Badge>
                                  </TableCell>
                                  <TableCell>
                                    <Button
                                      variant="ghost"
                                      size="sm"
                                      onClick={() => handleEditUser(vendor.masterUser)}
                                    >
                                      <UserCheck className="w-4 h-4" />
                                    </Button>
                                  </TableCell>
                                </TableRow>
                                {/* Regular Members */}
                                {vendor.members.map((member) => (
                                  <TableRow key={member.user.id}>
                                    <TableCell>{member.user.name}</TableCell>
                                    <TableCell>{member.user.email}</TableCell>
                                    <TableCell>
                                      {member.user.position && member.user.job_title
                                        ? `${member.user.position} / ${member.user.job_title}`
                                        : member.user.position || member.user.job_title || '-'
                                      }
                                    </TableCell>
                                    <TableCell>{formatDate(member.user.joined_at || member.user.created_at)}</TableCell>
                                    <TableCell>
                                      <Badge variant={getStatusBadgeVariant(member.user.account_status)}>
                                        {getStatusText(member.user.account_status)}
                                      </Badge>
                                    </TableCell>
                                    <TableCell>
                                      <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleEditUser(member.user)}
                                      >
                                        <UserCheck className="w-4 h-4" />
                                      </Button>
                                    </TableCell>
                                  </TableRow>
                                ))}
                              </TableBody>
                            </Table>
                          </div>
                        </div>
                      </div>
                    </CardContent>
                  )}
                </Card>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Edit Entity Modal */}
      <Dialog open={isEditModalOpen} onOpenChange={setIsEditModalOpen}>
        <DialogContent className="max-w-md">
          <DialogHeader>
            <DialogTitle>
              {editingType === 'agency' ? '대행사' : '용역사'} 정보 수정
            </DialogTitle>
          </DialogHeader>

          <div className="space-y-4">
            <div>
              <Label>회사명</Label>
              <Input
                value={editData.name || ''}
                onChange={(e) => setEditData(prev => ({ ...prev, name: e.target.value }))}
                placeholder="회사명 입력"
              />
            </div>

            <div>
              <Label>주소</Label>
              <Textarea
                value={editData.address || ''}
                onChange={(e) => setEditData(prev => ({ ...prev, address: e.target.value }))}
                placeholder="주소 입력"
                rows={2}
              />
            </div>

            {editingType === 'agency' && (
              <>
                <div>
                  <Label>구독 상태</Label>
                  <Select
                    value={editData.subscription_status || ''}
                    onValueChange={(value) => setEditData(prev => ({ ...prev, subscription_status: value }))}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="구독 상태 선택" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">활성</SelectItem>
                      <SelectItem value="inactive">비활성</SelectItem>
                      <SelectItem value="suspended">정지</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label>구독 만료일</Label>
                  <Input
                    type="date"
                    value={editData.subscription_end_date || ''}
                    onChange={(e) => setEditData(prev => ({ ...prev, subscription_end_date: e.target.value }))}
                  />
                </div>
              </>
            )}

            {editingType === 'vendor' && (
              <>
                <div>
                  <Label>상태</Label>
                  <Select
                    value={editData.status || ''}
                    onValueChange={(value) => setEditData(prev => ({ ...prev, status: value }))}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="상태 선택" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="active">활성</SelectItem>
                      <SelectItem value="inactive">비활성</SelectItem>
                      <SelectItem value="banned">차단</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {editData.status === 'banned' && (
                  <div>
                    <Label>차단 사유</Label>
                    <Textarea
                      value={editData.ban_reason || ''}
                      onChange={(e) => setEditData(prev => ({ ...prev, ban_reason: e.target.value }))}
                      placeholder="차단 사유 입력"
                      rows={3}
                    />
                  </div>
                )}
              </>
            )}
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setIsEditModalOpen(false)}>
              취소
            </Button>
            <Button onClick={handleSaveEntity} disabled={isSaving}>
              {isSaving ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  저장 중...
                </>
              ) : (
                <>
                  <Save className="w-4 h-4 mr-2" />
                  저장
                </>
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Edit User Status Modal */}
      <Dialog open={isUserModalOpen} onOpenChange={setIsUserModalOpen}>
        <DialogContent className="max-w-md">
          <DialogHeader>
            <DialogTitle>사용자 상태 변경</DialogTitle>
          </DialogHeader>

          <div className="space-y-4">
            {editingUser && (
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="font-medium">{editingUser.name}</p>
                <p className="text-sm text-gray-600">{editingUser.email}</p>
              </div>
            )}

            <div>
              <Label>계정 상태</Label>
              <Select
                value={userStatusData.account_status}
                onValueChange={(value) => setUserStatusData(prev => ({ ...prev, account_status: value }))}
              >
                <SelectTrigger>
                  <SelectValue placeholder="상태 선택" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pending">승인대기</SelectItem>
                  <SelectItem value="approved">승인완료</SelectItem>
                  <SelectItem value="rejected">승인거절</SelectItem>
                  <SelectItem value="suspended">계정정지</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div>
              <Label>관리자 메모</Label>
              <Textarea
                value={userStatusData.admin_notes}
                onChange={(e) => setUserStatusData(prev => ({ ...prev, admin_notes: e.target.value }))}
                placeholder="상태 변경 사유나 메모를 입력하세요"
                rows={3}
              />
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setIsUserModalOpen(false)}>
              취소
            </Button>
            <Button onClick={handleSaveUserStatus} disabled={isSaving}>
              {isSaving ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  업데이트 중...
                </>
              ) : (
                <>
                  <UserCheck className="w-4 h-4 mr-2" />
                  업데이트
                </>
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
