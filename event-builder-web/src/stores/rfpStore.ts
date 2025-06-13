import { create } from 'zustand'
import { devtools, persist } from 'zustand/middleware'

interface FeatureDetail {
  [key: string]: unknown
}

interface RfpBasicInfo {
  title: string;
  event_date: string;
  expected_attendees: string;
  total_budget: string;
  is_total_budget_undecided: boolean;
  description: string;
}

interface RfpStoreState {
  // RFP 기본 정보
  rfpBasicInfo: RfpBasicInfo;
  // 선택된 feature ID 배열
  selectedFeatures: number[];
  
  // 각 feature의 상세 설정 내용 (feature_id를 키로 사용)
  featureDetails: Record<number, FeatureDetail>;
  
  // 액션들
  updateRfpBasicInfo: (info: Partial<RfpBasicInfo>) => void;
  setSelectedFeatures: (features: number[]) => void;
  addSelectedFeature: (featureId: number) => void;
  removeSelectedFeature: (featureId: number) => void;
  toggleSelectedFeature: (featureId: number) => void;
  
  updateFeatureDetail: (featureId: number, details: FeatureDetail) => void;
  updateFeatureDetailField: (featureId: number, field: string, value: unknown) => void;
  
  // 전체 상태 초기화
  resetRfpStore: () => void;
}

interface RfpStoreActions {
  setRfpBasicInfo: (info: RfpBasicInfo) => void;
  toggleSelectedFeature: (featureId: number) => void;
  setSelectedFeatures: (featureIds: number[]) => void;
  setRfpData: (data: { basicInfo: RfpBasicInfo; selectedFeatures: number[] }) => void;
  resetRfpStore: () => void;
}

const initialState = {
  rfpBasicInfo: {
    title: '',
    event_date: '',
    expected_attendees: '',
    total_budget: '',
    is_total_budget_undecided: false,
    description: '',
  },
  selectedFeatures: [],
  featureDetails: {},
}

export const useRfpStore = create<RfpStoreState & RfpStoreActions>()(
  devtools(
    persist(
      (set, get) => ({
        rfpBasicInfo: initialState.rfpBasicInfo,
        selectedFeatures: initialState.selectedFeatures,
        featureDetails: initialState.featureDetails,

        // RFP 기본 정보 업데이트
        updateRfpBasicInfo: (info: Partial<RfpBasicInfo>) =>
          set(
            (state) => ({
              rfpBasicInfo: {
                ...state.rfpBasicInfo,
                ...info,
              },
            }),
            false,
            'updateRfpBasicInfo'
          ),

        // 선택된 features 전체 설정
        setSelectedFeatures: (features: number[]) =>
          set({ selectedFeatures: features }, false, 'setSelectedFeatures'),

        // 단일 feature 추가
        addSelectedFeature: (featureId: number) =>
          set(
            (state) => ({
              selectedFeatures: state.selectedFeatures.includes(featureId)
                ? state.selectedFeatures
                : [...state.selectedFeatures, featureId],
            }),
            false,
            'addSelectedFeature'
          ),

        // 단일 feature 제거
        removeSelectedFeature: (featureId: number) =>
          set(
            (state) => ({
              selectedFeatures: state.selectedFeatures.filter(id => id !== featureId),
              featureDetails: Object.fromEntries(
                Object.entries(state.featureDetails).filter(([key]) => parseInt(key) !== featureId)
              ),
            }),
            false,
            'removeSelectedFeature'
          ),

        // 단일 feature 토글 (선택/해제)
        toggleSelectedFeature: (featureId: number) => {
          const { selectedFeatures } = get()
          if (selectedFeatures.includes(featureId)) {
            get().removeSelectedFeature(featureId)
          } else {
            get().addSelectedFeature(featureId)
          }
        },

        // feature의 상세 설정 전체 업데이트
        updateFeatureDetail: (featureId: number, details: FeatureDetail) =>
          set(
            (state) => ({
              featureDetails: {
                ...state.featureDetails,
                [featureId]: details,
              },
            }),
            false,
            'updateFeatureDetail'
          ),

        // feature의 특정 필드만 업데이트
        updateFeatureDetailField: (featureId: number, field: string, value: unknown) =>
          set(
            (state) => ({
              featureDetails: {
                ...state.featureDetails,
                [featureId]: {
                  ...state.featureDetails[featureId],
                  [field]: value,
                },
              },
            }),
            false,
            'updateFeatureDetailField'
          ),

        // 전체 상태 초기화
        resetRfpStore: () =>
          set(initialState, false, 'resetRfpStore'),

        setRfpBasicInfo: (info: RfpBasicInfo) =>
          set(
            () => ({
              rfpBasicInfo: info,
            }),
            false,
            'setRfpBasicInfo'
          ),

        setRfpData: (data: { basicInfo: RfpBasicInfo; selectedFeatures: number[] }) =>
          set(() => ({
            rfpBasicInfo: data.basicInfo,
            selectedFeatures: data.selectedFeatures,
          })),
      }),
      {
        name: 'rfp-store', // localStorage 키
        // 민감한 정보는 persist하지 않을 수 있음
      }
    ),
    {
      name: 'rfp-store', // devtools 이름
    }
  )
) 