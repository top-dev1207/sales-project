// const defaultTheme = require('tailwindcss/defaultTheme');
// import type { Config } from "tailwindcss";

// module.exports = {
//             content: [
//                 // './resources/**/*.blade.php',
//                 './resources/js/**/*.tsx',
//                 './resources/js/**/*.ts',
//                 // './resources/js/**/*.jsx',
//                 // './resources/js/**/*.js'
//               ],

//     theme: {
//         extend: {
//             fontFamily: {
//                 sans: ['Nunito', ...defaultTheme.fontFamily.sans],
//             },
//             colors: {
//                 border: 'hsl(var(--border))',
// 				input: 'hsl(var(--input))',
// 				ring: 'hsl(var(--ring))',
// 				background: 'hsl(var(--background))',
// 				foreground: 'hsl(var(--foreground))',
// 				primary: {
// 					DEFAULT: 'hsl(var(--primary))',
// 					foreground: 'hsl(var(--primary-foreground))'
// 				},
// 				secondary: {
// 					DEFAULT: 'hsl(var(--secondary))',
// 					foreground: 'hsl(var(--secondary-foreground))'
// 				},
// 				destructive: {
// 					DEFAULT: 'hsl(var(--destructive))',
// 					foreground: 'hsl(var(--destructive-foreground))'
// 				},
// 				muted: {
// 					DEFAULT: 'hsl(var(--muted))',
// 					foreground: 'hsl(var(--muted-foreground))'
// 				},
// 				accent: {
// 					DEFAULT: 'hsl(var(--accent))',
// 					foreground: 'hsl(var(--accent-foreground))'
// 				},
// 				popover: {
// 					DEFAULT: 'hsl(var(--popover))',
// 					foreground: 'hsl(var(--popover-foreground))'
// 				},
// 				card: {
// 					DEFAULT: 'hsl(var(--card))',
// 					foreground: 'hsl(var(--card-foreground))'
// 				},
// 				sidebar: {
// 					DEFAULT: 'hsl(var(--sidebar-background))',
// 					foreground: 'hsl(var(--sidebar-foreground))',
// 					primary: 'hsl(var(--sidebar-primary))',
// 					'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
// 					accent: 'hsl(var(--sidebar-accent))',
// 					'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
// 					border: 'hsl(var(--sidebar-border))',
// 					ring: 'hsl(var(--sidebar-ring))'
// 				},
// 				// Updated dashboard colors with a more cohesive palette
// 				dashboard: {
// 					blue: '#3B82F6',
// 					green: '#10B981',
// 					amber: '#F59E0B',
// 					red: '#EF4444',
// 					purple: '#8B5CF6',
// 					teal: '#14B8A6',
// 					indigo: '#6366F1',
// 					cyan: '#06B6D4'
// 				},
//                 // primary: '#4361ee',
//                 'primary-dark': '#3a56d4',
//                 good: '#4ade80',
//                 warning: '#facc15',
//                 critical: '#f87171',
//                 neutral: '#94a3b8',
//             },
//             borderRadius: {
// 				lg: 'var(--radius)',
// 				md: 'calc(var(--radius) - 2px)',
// 				sm: 'calc(var(--radius) - 4px)'
// 			},
//             keyframes: {
// 				'accordion-down': {
// 					from: {
// 						height: '0'
// 					},
// 					to: {
// 						height: 'var(--radix-accordion-content-height)'
// 					}
// 				},
// 				'accordion-up': {
// 					from: {
// 						height: 'var(--radix-accordion-content-height)'
// 					},
// 					to: {
// 						height: '0'
// 					}
// 				},
// 				"fade-in": {
// 					"0%": {
// 						opacity: "0",
// 						transform: "translateY(10px)"
// 					},
// 					"100%": {
// 						opacity: "1",
// 						transform: "translateY(0)"
// 					}
// 				}
// 			},
//             animation: {
// 				'accordion-down': 'accordion-down 0.2s ease-out',
// 				'accordion-up': 'accordion-up 0.2s ease-out',
// 				"fade-in": "fade-in 0.3s ease-out",
// 			},
// 			boxShadow: {
// 				'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
// 				'header': '0 1px 3px rgba(0, 0, 0, 0.05)'
// 			}
//         },
//     },

//     variants: {
//         extend: {
//             opacity: ['disabled'],
//         },
//     },

//     // plugins: [require('@tailwindcss/forms')],
//     plugins: [require("tailwindcss-animate")],
// }satisfies Config;

import type { Config } from "tailwindcss";

export default {
	darkMode: ["class"],
    content: [
                        './resources/**/*.blade.php',
                        './resources/js/**/*.tsx',
                        './resources/js/**/*.ts',
                        './resources/js/**/*.jsx',
                        './resources/js/**/*.js'
                      ],
	prefix: "",
	theme: {
		container: {
			center: true,
			padding: '2rem',
			screens: {
				'2xl': '1400px'
			}
		},
		extend: {
			colors: {
				border: 'hsl(var(--border))',
				input: 'hsl(var(--input))',
				ring: 'hsl(var(--ring))',
				background: 'hsl(var(--background))',
				foreground: 'hsl(var(--foreground))',
				primary: {
					DEFAULT: 'hsl(var(--primary))',
					foreground: 'hsl(var(--primary-foreground))'
				},
				secondary: {
					DEFAULT: 'hsl(var(--secondary))',
					foreground: 'hsl(var(--secondary-foreground))'
				},
				destructive: {
					DEFAULT: 'hsl(var(--destructive))',
					foreground: 'hsl(var(--destructive-foreground))'
				},
				muted: {
					DEFAULT: 'hsl(var(--muted))',
					foreground: 'hsl(var(--muted-foreground))'
				},
				accent: {
					DEFAULT: 'hsl(var(--accent))',
					foreground: 'hsl(var(--accent-foreground))'
				},
				popover: {
					DEFAULT: 'hsl(var(--popover))',
					foreground: 'hsl(var(--popover-foreground))'
				},
				card: {
					DEFAULT: 'hsl(var(--card))',
					foreground: 'hsl(var(--card-foreground))'
				},
				sidebar: {
					DEFAULT: 'hsl(var(--sidebar-background))',
					foreground: 'hsl(var(--sidebar-foreground))',
					primary: 'hsl(var(--sidebar-primary))',
					'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
					accent: 'hsl(var(--sidebar-accent))',
					'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
					border: 'hsl(var(--sidebar-border))',
					ring: 'hsl(var(--sidebar-ring))'
				},
				// Updated dashboard colors with a more cohesive palette
				dashboard: {
					blue: '#3B82F6',
					green: '#10B981',
					amber: '#F59E0B',
					red: '#EF4444',
					purple: '#8B5CF6',
					teal: '#14B8A6',
					indigo: '#6366F1',
					cyan: '#06B6D4'
				}
			},
			borderRadius: {
				lg: 'var(--radius)',
				md: 'calc(var(--radius) - 2px)',
				sm: 'calc(var(--radius) - 4px)'
			},
			keyframes: {
				'accordion-down': {
					from: {
						height: '0'
					},
					to: {
						height: 'var(--radix-accordion-content-height)'
					}
				},
				'accordion-up': {
					from: {
						height: 'var(--radix-accordion-content-height)'
					},
					to: {
						height: '0'
					}
				},
				"fade-in": {
					"0%": {
						opacity: "0",
						transform: "translateY(10px)"
					},
					"100%": {
						opacity: "1",
						transform: "translateY(0)"
					}
				}
			},
			animation: {
				'accordion-down': 'accordion-down 0.2s ease-out',
				'accordion-up': 'accordion-up 0.2s ease-out',
				"fade-in": "fade-in 0.3s ease-out",
			},
			boxShadow: {
				'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
				'header': '0 1px 3px rgba(0, 0, 0, 0.05)'
			}
		}
	},
	plugins: [require("tailwindcss-animate")],
} satisfies Config;
