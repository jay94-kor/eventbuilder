// 대시보드 통계 데이터 타입 정의

export interface DashboardStats {
  total_rfps: number;
  completed_rfps: number;
  monthly_rfp_counts: Record<string, number>; // { "2025-01": 2, "2025-02": 1, ... }
  top_features: TopFeature[];
}

export interface TopFeature {
  id: number;
  name: string;
  icon: string;
  usage_count: number;
}

// 월별 차트 데이터 (Recharts용)
export interface MonthlyChartData {
  month: string;
  count: number;
  displayMonth: string; // "1월", "2월" 등 표시용
}

// 통계 카드 데이터
export interface StatCard {
  title: string;
  value: number | string;
  icon: React.ComponentType<Record<string, unknown>>;
  color: string;
  bgColor: string;
  description?: string;
} 