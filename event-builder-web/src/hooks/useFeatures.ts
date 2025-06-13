'use client'

import { useState, useEffect } from 'react'
import { FeatureCategory } from '@/types/rfp'
import { apiClient } from '@/lib/api'

export function useFeatures() {
  const [features, setFeatures] = useState<FeatureCategory[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchFeatures() {
      try {
        setLoading(true)
        const response = await apiClient.get<FeatureCategory[]>('/features')
        if (response.success) {
          setFeatures(response.data)
        } else {
          setError(response.message || 'Failed to fetch features')
        }
      } catch {
        setError('Network error occurred')
      } finally {
        setLoading(false)
      }
    }

    fetchFeatures()
  }, [])

  return { features, loading, error }
} 