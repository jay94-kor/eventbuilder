'use client';

import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/lib/i18n';

interface ErrorBoundaryProps {
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

const ErrorBoundary: React.FC<ErrorBoundaryProps> = ({ children, fallback }) => {
  const [hasError, setHasError] = useState(false);
  const [error, setError] = useState<Error | undefined>(undefined);
  const { t } = useTranslation();

  useEffect(() => {
    const errorHandler = (event: ErrorEvent) => {
      setHasError(true);
      setError(event.error);
      console.error('ErrorBoundary caught an error:', event.error);
    };

    const promiseRejectionHandler = (event: PromiseRejectionEvent) => {
      setHasError(true);
      setError(new Error(event.reason));
      console.error('ErrorBoundary caught an unhandled promise rejection:', event.reason);
    };

    window.addEventListener('error', errorHandler);
    window.addEventListener('unhandledrejection', promiseRejectionHandler);

    return () => {
      window.removeEventListener('error', errorHandler);
      window.removeEventListener('unhandledrejection', promiseRejectionHandler);
    };
  }, []);

  if (hasError) {
    if (fallback) {
      return fallback;
    }

    return (
      <div className="min-h-screen bg-background flex items-center justify-center p-4">
        <div className="max-w-md w-full bg-white rounded-lg shadow-lg p-6 text-center">
          <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg
              className="w-8 h-8 text-destructive"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
              />
            </svg>
          </div>
          <h2 className="text-heading-md mb-2">
            {t('errorBoundary.title')}
          </h2>
          <p className="text-muted-foreground mb-4">
            {t('errorBoundary.description')}
          </p>
          <Button
            onClick={() => window.location.reload()}
            variant="default"
            className="w-full"
          >
            {t('errorBoundary.reloadButton')}
          </Button>
          {process.env.NODE_ENV === 'development' && (
            <details className="mt-4 text-left">
              <summary className="cursor-pointer text-sm text-muted-foreground">
                {t('errorBoundary.errorDetails')}
              </summary>
              <pre className="mt-2 text-xs text-destructive bg-destructive/10 p-2 rounded overflow-auto">
                {error?.stack}
              </pre>
            </details>
          )}
        </div>
      </div>
    );
  }

  return children;
};

export default ErrorBoundary;