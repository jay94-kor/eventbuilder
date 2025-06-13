'use client';

import React, { useState, useEffect } from 'react';
import { UpdateUserData } from '@/lib/api';
import { User } from '@/types/auth'; // User 타입 임포트
import { useAuth } from '@/hooks/useAuth';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import { useTranslation } from '@/lib/i18n';

interface ProfileFormProps {
  user: User;
  onCancel: () => void;
}

export default function ProfileForm({ user, onCancel }: ProfileFormProps) {
  const { updateUser, isLoading, error, clearError } = useAuth();
  const t = useTranslation();
  const [formData, setFormData] = useState({
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
  });
  const [formErrors, setFormErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (error) {
      toast.error(t('profile.update_failed_toast_title'), {
        description: error,
        duration: 5000,
      });
      clearError();
    }
  }, [error, clearError]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    if (formErrors[name]) {
      setFormErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  const validateForm = () => {
    const errors: Record<string, string> = {};
    if (!formData.name.trim()) {
      errors.name = t('auth.register.name_required');
    }
    if (!formData.email.trim()) {
      errors.email = t('auth.login.email_required');
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      errors.email = t('auth.login.email_invalid');
    }
    if (formData.password && formData.password.length < 8) {
      errors.password = t('auth.register.password_min_length');
    }
    if (formData.password && formData.password !== formData.password_confirmation) {
      errors.password_confirmation = t('auth.register.password_mismatch');
    }
    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError(); // 이전 에러 초기화

    if (!validateForm()) {
      toast.error(t('profile.input_error_toast_title'), {
        description: t('profile.input_error_toast_description'),
        duration: 5000,
      });
      return;
    }

    try {
      const dataToUpdate: UpdateUserData = { // Partial<User> 대신 UpdateUserData 사용
        name: formData.name,
        email: formData.email,
      };
      if (formData.password) {
        dataToUpdate.password = formData.password;
        dataToUpdate.password_confirmation = formData.password_confirmation;
      }

      await updateUser(dataToUpdate);
      toast.success(t('profile.update_success_toast_title'), {
        description: t('profile.update_success_toast_description'),
        duration: 3000,
      });
      onCancel();
    } catch (err) {
      // useAuth 훅에서 이미 에러를 설정하고 토스트를 띄우므로 여기서는 추가 처리 불필요
      console.error('Profile update failed:', err);
    }
  };

  return (
    <>
      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label htmlFor="name" className="block text-label mb-1">
            {t('profile.name_label')}
          </label>
          <Input
            type="text"
            id="name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            placeholder={t('auth.register.name_placeholder')}
          />
          {formErrors.name && (
            <p className="mt-1 text-sm text-destructive">{formErrors.name}</p>
          )}
        </div>

        <div>
          <label htmlFor="email" className="block text-label mb-1">
            {t('profile.email_label')}
          </label>
          <Input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
            placeholder={t('auth.login.email_placeholder')}
          />
          {formErrors.email && (
            <p className="mt-1 text-sm text-destructive">{formErrors.email}</p>
          )}
        </div>

        <div>
          <label htmlFor="password" className="block text-label mb-1">
            {t('profile.new_password_label')}
          </label>
          <Input
            type="password"
            id="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            placeholder={t('profile.new_password_placeholder')}
          />
          {formErrors.password && (
            <p className="mt-1 text-sm text-destructive">{formErrors.password}</p>
          )}
        </div>

        <div>
          <label htmlFor="password_confirmation" className="block text-label mb-1">
            {t('profile.confirm_new_password_label')}
          </label>
          <Input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            value={formData.password_confirmation}
            onChange={handleChange}
            placeholder={t('profile.confirm_new_password_placeholder')}
          />
          {formErrors.password_confirmation && (
            <p className="mt-1 text-sm text-destructive">{formErrors.password_confirmation}</p>
          )}
        </div>

        <div className="flex justify-end space-x-3">
          <Button type="button" variant="outline" onClick={onCancel} disabled={isLoading}>
            {t('profile.cancel_button')}
          </Button>
          <Button type="submit" disabled={isLoading}>
            {isLoading ? t('profile.saving_button') : t('profile.save_info_button')}
          </Button>
        </div>
      </form>
    </>
  );
}