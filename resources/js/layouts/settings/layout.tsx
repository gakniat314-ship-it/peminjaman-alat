import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/settings/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
        icon: null,
    },
    {
        title: 'Security',
        href: editSecurity(),
        icon: null,
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: null,
    },
];

/** Menyusun halaman pengaturan dengan navigasi profil, keamanan, dan tampilan. */
export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="space-y-6 px-4 py-6 sm:px-6">
            <Heading
                title="Settings"
                description="Manage your profile and account settings"
            />

            <div className="grid gap-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:items-start">
                <aside className="w-full rounded-2xl border border-border bg-card p-2 shadow-sm">
                    <nav
                        className="flex gap-1 overflow-x-auto lg:flex-col"
                        aria-label="Settings"
                    >
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${toUrl(item.href)}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn(
                                    'w-auto shrink-0 justify-start rounded-xl px-3 py-2.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground lg:w-full',
                                    {
                                        'bg-accent font-semibold text-accent-foreground hover:bg-accent':
                                            isCurrentOrParentUrl(item.href),
                                    },
                                )}
                            >
                                <Link href={item.href}>
                                    {item.icon && (
                                        <item.icon className="h-4 w-4" />
                                    )}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-2xl space-y-8">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
