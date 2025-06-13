export interface FeatureCategory {
  id: number
  name: string
  order: number
  created_at: string
  updated_at: string
  features?: Feature[]
}

export interface Feature {
  id: number
  name: string
  icon?: string
  description?: string
  is_premium?: boolean
  budget_allocation?: boolean
  internal_resource_flag?: boolean
  feature_category_id: number
  recommendations?: Feature[]
  created_at: string
  updated_at: string
  category?: FeatureCategory
  config?: {
    fields: FeatureField[]
  }
}

export interface FeatureField {
  key: string
  name: string
  type: 'text' | 'number' | 'textarea' | 'select' | 'radio' | 'checkbox' | 'date' | 'time' | 'datetime'
  placeholder?: string
  required?: boolean
  unit?: string
  show_unit?: boolean
  options?: { label: string; value: string | number }[]
  field_level?: 'independent' | 'parent' | 'child'
  parent_field?: string
  show_when_value?: string | number | boolean
  allow_undecided?: boolean
  feature_scope?: 'by_zone' | 'overall'
  feature_zones?: string[]
  internal_resource?: boolean
  internal_resource_person?: string
}

export interface FeatureWithRecommendations extends Feature {
  recommendations: Feature[]
}

export interface Rfp {
  id: number
  title: string
  status: 'draft' | 'completed' | 'archived'
  user_id: number
  created_at: string
  updated_at: string
  selections?: RfpSelection[]
}

export interface RfpSelection {
  id: number
  rfp_id: number
  feature_id: number
  details: Record<string, unknown>
  created_at: string
  updated_at: string
  feature?: Feature
}

export interface CreateRfpData {
  title: string
  event_date?: string
  selections: Array<{
    feature_id: number
    details: Record<string, unknown>
  }>
}

export interface RfpStore {
  currentRfp: Partial<Rfp> | null
  selectedFeatures: number[]
  featureDetails: Record<number, Record<string, unknown>>
  setTitle: (title: string) => void
  toggleFeature: (featureId: number) => void
  setFeatureDetails: (featureId: number, details: Record<string, unknown>) => void
  clearCurrentRfp: () => void
  createRfp: () => Promise<void>
}

export interface RfpFormProps {
  initialData?: Rfp;
}

export interface RecommendedFeature extends Feature {
  pivot?: {
    level: 'R1' | 'R2';
  };
}