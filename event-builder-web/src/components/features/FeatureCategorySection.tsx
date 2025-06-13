interface FeatureCategorySectionProps {
  categoryName: string
  features: Array<{
    id: number
    name: string
    icon: string
    description?: string
  }>
  selectedFeatures?: number[]
  onFeatureSelect?: (featureId: number) => void
}

export default function FeatureCategorySection({
  categoryName,
  features,
  selectedFeatures = [],
  onFeatureSelect
}: FeatureCategorySectionProps) {
  return (
    <div className="mb-8">
      <h2 className="text-heading-md mb-4">{categoryName}</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {features.map((feature) => (
          <div
            key={feature.id}
            className={`p-4 border rounded-lg cursor-pointer ${
              selectedFeatures.includes(feature.id) 
                ? 'border-primary bg-primary/10'
                : 'border'
            }`}
            onClick={() => onFeatureSelect?.(feature.id)}
          >
            <div className="text-2xl mb-2">{feature.icon}</div>
            <h3 className="font-semibold">{feature.name}</h3>
            {feature.description && (
              <p className="text-description mt-1">{feature.description}</p>
            )}
          </div>
        ))}
      </div>
    </div>
  )
} 