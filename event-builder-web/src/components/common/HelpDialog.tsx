'use client';

import React, { useState } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { HelpCircleIcon } from 'lucide-react';
import { useTranslation } from '@/lib/i18n';
import { ScrollArea } from '@/components/ui/scroll-area';

interface HelpDialogProps {
  children?: React.ReactNode;
}

const HelpDialog: React.FC<HelpDialogProps> = ({ children }) => {
  const [isOpen, setIsOpen] = useState(false);
  const { t } = useTranslation();

  const helpContent = [
    {
      title: t('help.step1_title'),
      description: t('help.step1_description'),
      icon: '📋',
      color: 'from-blue-500 to-indigo-600'
    },
    {
      title: t('help.step2_title'),
      description: t('help.step2_description'),
      icon: '🎯',
      color: 'from-green-500 to-emerald-600'
    },
    {
      title: t('help.step3_title'),
      description: t('help.step3_description'),
      icon: '⚙️',
      color: 'from-purple-500 to-pink-600'
    },
    {
      title: t('help.step4_title'),
      description: t('help.step4_description'),
      icon: '🚀',
      color: 'from-orange-500 to-red-600'
    },
  ];

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        {children || (
          <Button variant="ghost" size="icon" className="text-muted-foreground hover:text-foreground">
            <HelpCircleIcon className="h-6 w-6" />
            <span className="sr-only">{t('help.button_text')}</span>
          </Button>
        )}
      </DialogTrigger>
      <DialogContent className="sm:max-w-[900px] h-[85vh] flex flex-col bg-gradient-to-br from-white to-gray-50">
        <DialogHeader className="pb-6 border-b border-gray-200">
          <div className="flex items-center gap-3 mb-2">
            <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
              <span className="text-white text-xl">❓</span>
            </div>
            <DialogTitle className="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
              {t('help.dialog_title')}
            </DialogTitle>
          </div>
          <DialogDescription className="text-gray-600 text-base">
            {t('help.dialog_description')}
          </DialogDescription>
        </DialogHeader>
        
        <ScrollArea className="flex-grow pr-4 -mr-4">
          <div className="space-y-6 py-6">
            {helpContent.map((step, index) => (
              <div key={index} className="group">
                <div className="flex items-start gap-4 p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-gray-200">
                  <div className={`w-16 h-16 bg-gradient-to-br ${step.color} rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300`}>
                    <span className="text-white text-2xl">{step.icon}</span>
                  </div>
                  
                  <div className="flex-1">
                    <h3 className="text-xl font-bold text-gray-900 mb-3 flex items-center gap-2">
                      <span className="w-8 h-8 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center text-sm font-bold text-gray-600">
                        {index + 1}
                      </span>
                      {step.title}
                    </h3>
                    <p className="text-gray-600 leading-relaxed text-base">
                      {step.description}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </ScrollArea>
        
        <div className="flex justify-center pt-6 border-t border-gray-200">
          <Button 
            onClick={() => setIsOpen(false)}
            className="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-200"
          >
            {t('help.close_button')}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default HelpDialog;