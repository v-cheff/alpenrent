export const locations = [
  { id: 'innsbruck', label: 'Innsbruck Central Store', hint: 'City pickup · 08:00–20:00' },
  { id: 'stanton', label: 'St. Anton Base Station', hint: 'Slope-side · 07:30–18:00' },
  { id: 'solden', label: 'Sölden Glacier Hub', hint: 'Valley station · 08:00–18:30' },
  { id: 'kitz', label: 'Kitzbühel Town Shop', hint: 'Hahnenkamm · 08:00–19:00' },
]

export const skillLevels = [
  { id: 'beginner', label: 'Beginner', description: 'Green / easy blue runs' },
  { id: 'intermediate', label: 'Intermediate', description: 'All-mountain piste' },
  { id: 'pro', label: 'Pro', description: 'Steeps, park & powder' },
]

export const catalogTabs = [
  { id: 'all', label: 'All Equipment' },
  { id: 'ski', label: 'Ski Sets' },
  { id: 'board', label: 'Snowboards' },
  { id: 'boots', label: 'Boots & Helmets' },
  { id: 'kids', label: 'Kids Sets' },
]

export const equipment = [
  {
    id: 'atomic-redster',
    name: 'Atomic Redster S9 Revoshock',
    category: 'ski',
    categoryLabel: 'Performance Ski Set',
    badge: 'Model 2026',
    badgeTone: 'new',
    image:
      'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?auto=format&fit=crop&w=900&q=80',
    specs: ['Radius 13.5 m', 'Camber race profile', 'Includes poles'],
    dailyRate: 49,
    deposit: 150,
    sizes: ['150', '157', '165', '172'],
  },
  {
    id: 'rossignol-experience',
    name: 'Rossignol Experience 86 Basalt',
    category: 'ski',
    categoryLabel: 'All-Mountain Ski Set',
    badge: 'Freshly Waxed',
    badgeTone: 'waxed',
    image:
      'https://images.unsplash.com/photo-1605540436563-5bca919ae766?auto=format&fit=crop&w=900&q=80',
    specs: ['Radius 16 m', 'Air Tip rocker', 'ISO 11088 bindings'],
    dailyRate: 39,
    deposit: 80,
    sizes: ['146', '154', '162', '170', '178'],
  },
  {
    id: 'head-kore',
    name: 'HEAD Kore 93',
    category: 'ski',
    categoryLabel: 'Freeride Ski Set',
    badge: 'Freshly Waxed',
    badgeTone: 'waxed',
    image:
      'https://images.unsplash.com/photo-1483664852095-d6cc6870703d?auto=format&fit=crop&w=900&q=80',
    specs: ['Radius 17.8 m', 'Tip & tail rocker', 'Carbon sandwich'],
    dailyRate: 45,
    deposit: 120,
    sizes: ['156', '163', '170', '177', '184'],
  },
  {
    id: 'burton-custom',
    name: 'Burton Custom Camber',
    category: 'board',
    categoryLabel: 'All-Mountain Snowboard',
    badge: 'Model 2026',
    badgeTone: 'new',
    image:
      'https://images.unsplash.com/photo-1522056615691-da7b8106c665?auto=format&fit=crop&w=900&q=80',
    specs: ['Camber pop', 'Directional twin', 'Channel bindings'],
    dailyRate: 42,
    deposit: 100,
    sizes: ['148', '152', '156', '158W', '162'],
  },
  {
    id: 'jones-mind',
    name: 'Jones Mind Expander',
    category: 'board',
    categoryLabel: 'Powder Snowboard',
    badge: 'Freshly Waxed',
    badgeTone: 'waxed',
    image:
      'https://images.unsplash.com/photo-1518085250887-2f833c18b9d4?auto=format&fit=crop&w=900&q=80',
    specs: ['Spoon 3.0 nose', '3D contour base', 'Swallow tail'],
    dailyRate: 48,
    deposit: 130,
    sizes: ['146', '150', '154', '158'],
  },
  {
    id: 'lange-shadow',
    name: 'Lange Shadow 120 LV',
    category: 'boots',
    categoryLabel: 'Alpine Ski Boots',
    badge: 'Heat-moldable',
    badgeTone: 'info',
    image:
      'https://images.unsplash.com/photo-1578662996442-48f36e048e72?auto=format&fit=crop&w=900&q=80',
    specs: ['Flex 120', 'Last 97 mm', 'GripWalk soles'],
    dailyRate: 22,
    deposit: 50,
    sizes: ['24.5', '25.5', '26.5', '27.5', '28.5'],
  },
  {
    id: 'poc-obex',
    name: 'POC Obex BC MIPS + Visor',
    category: 'boots',
    categoryLabel: 'Helmet & Protection',
    badge: 'MIPS',
    badgeTone: 'info',
    image:
      'https://images.unsplash.com/photo-1516731415730-0c1374e67dba?auto=format&fit=crop&w=900&q=80',
    specs: ['MIPS liner', 'RECCO reflector', 'Category 3 visor'],
    dailyRate: 12,
    deposit: 40,
    sizes: ['XS/S', 'M/L', 'XL'],
  },
  {
    id: 'kids-atomic',
    name: 'Atomic Maven Girl / Redster J',
    category: 'kids',
    categoryLabel: 'Kids Ski Set',
    badge: 'Kids 4–12',
    badgeTone: 'kids',
    image:
      'https://images.unsplash.com/photo-1548777123-e27896422455?auto=format&fit=crop&w=900&q=80',
    specs: ['Soft flex bindings', 'Helmet included', 'Parent-friendly size chart'],
    dailyRate: 24,
    deposit: 40,
    sizes: ['90', '100', '110', '120', '130'],
  },
  {
    id: 'kids-burton',
    name: 'Burton Mini Grom Package',
    category: 'kids',
    categoryLabel: 'Kids Snowboard Set',
    badge: 'Kids 5–12',
    badgeTone: 'kids',
    image:
      'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=80',
    specs: ['Soft bindings', 'Easy-entry boots', 'Park-ready twin'],
    dailyRate: 26,
    deposit: 45,
    sizes: ['110', '120', '130', '134'],
  },
]

export const reviews = [
  {
    name: 'Lena K.',
    city: 'Munich',
    rating: 5,
    text: 'Bindings adjusted to ISO 11088 in minutes. Skis felt factory-fresh on the first run in Sölden.',
  },
  {
    name: 'Jonas W.',
    city: 'Vienna',
    rating: 5,
    text: 'Picked up in Innsbruck, swapped a board at St. Anton — no extra fee. Exactly what you want in Tyrol.',
  },
  {
    name: 'Claire D.',
    city: 'Lyon',
    rating: 4,
    text: 'Clear VAT-inclusive pricing and a real deposit receipt. Kids set was sized correctly first try.',
  },
]

export const sizingGuide = [
  { height: '140–149 cm', ski: '130–140 cm', board: '128–138 cm' },
  { height: '150–159 cm', ski: '140–150 cm', board: '138–148 cm' },
  { height: '160–169 cm', ski: '150–160 cm', board: '148–154 cm' },
  { height: '170–179 cm', ski: '160–170 cm', board: '154–158 cm' },
  { height: '180–189 cm', ski: '170–180 cm', board: '158–164 cm' },
  { height: '190+ cm', ski: '180–190 cm', board: '162–168 cm' },
]
