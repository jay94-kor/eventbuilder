/**
 * 온보딩 화면에서 사용할 예시 RFP 데이터
 */

export interface OnboardingRfp {
  id: number;
  title: string;
  event_date: string;
  status: '완료' | '진행중' | '준비중' | '예정';
  color: 'blue' | 'green' | 'purple' | 'orange' | 'pink' | 'indigo';
  description?: string;
}

export const onboardingRfpData: OnboardingRfp[] = [
  {
    id: 1,
    title: "2024 상반기 신제품 런칭쇼",
    event_date: "2024-03-15",
    status: "완료",
    color: "blue",
    description: "혁신적인 신제품 발표를 위한 대규모 런칭 이벤트"
  },
  {
    id: 2,
    title: "창립 10주년 기념 갈라디너",
    event_date: "2024-05-20",
    status: "완료",
    color: "green",
    description: "회사 창립 10주년을 축하하는 특별한 만찬 행사"
  },
  {
    id: 3,
    title: "2024 하계 워크샵 & 팀빌딩",
    event_date: "2024-07-12",
    status: "진행중",
    color: "purple",
    description: "전 직원 대상 여름 워크샵 및 팀빌딩 프로그램"
  },
  {
    id: 4,
    title: "고객 감사 이벤트",
    event_date: "2024-09-08",
    status: "준비중",
    color: "orange",
    description: "소중한 고객들을 위한 특별 감사 이벤트"
  },
  {
    id: 5,
    title: "연말 시상식 & 파티",
    event_date: "2024-12-15",
    status: "예정",
    color: "pink",
    description: "한 해를 마무리하는 시상식 및 송년회"
  },
  {
    id: 6,
    title: "2025 신년 킥오프 미팅",
    event_date: "2025-01-10",
    status: "예정",
    color: "indigo",
    description: "새로운 한 해를 시작하는 전략 수립 미팅"
  }
];

/**
 * 상태별 색상 매핑
 */
export const statusColorMap = {
  '완료': 'text-green-600 bg-green-100',
  '진행중': 'text-blue-600 bg-blue-100', 
  '준비중': 'text-yellow-600 bg-yellow-100',
  '예정': 'text-gray-600 bg-gray-100'
};

/**
 * 카드 색상별 CSS 클래스 매핑
 */
export const cardColorMap = {
  blue: 'bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700',
  green: 'bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700',
  purple: 'bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700',
  orange: 'bg-gradient-to-br from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700',
  pink: 'bg-gradient-to-br from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700',
  indigo: 'bg-gradient-to-br from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700'
}; 