import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppearanceToggleDropdown from '@/components/appearance-dropdown';
import { 
    FileText, 
    Clock, 
    CheckCircle, 
    XCircle, 
    AlertCircle, 
    BarChart3,
    Calendar,
    Database,
    Monitor,
    Target
} from 'lucide-react';

interface Report {
    id: number;
    filename: string;
    version: string;
    campaign: string;
    platform: string;
    database: string;
    start_date: string;
    duration: number;
    tests: number;
    passes: number;
    failures: number;
    pending: number;
    skipped: number;
}

interface WelcomeProps {
    reports: Report[];
    stats: {
        totalReports: number;
        totalTests: number;
        avgSuccessRate: number;
        recentReports: number;
    };
}

export default function Welcome({ reports = [], stats = { totalReports: 0, totalTests: 0, avgSuccessRate: 0, recentReports: 0 } }: WelcomeProps) {
    const { auth } = usePage<SharedData>().props;

    const getStatusColor = (passes: number, failures: number, total: number) => {
        if (failures === 0) return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        if (failures / total > 0.1) return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
    };

    const getSuccessRate = (passes: number, total: number) => {
        return total > 0 ? ((passes / total) * 100).toFixed(1) : '0.0';
    };

    return (
        <>
            <Head title="Playwright Report Hub">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-background">
                <header className="border-b bg-card/50 backdrop-blur supports-[backdrop-filter]:bg-card/50">
                    <div className="container mx-auto px-4 py-4">
                        <nav className="flex items-center justify-between">
                            <div className="flex items-center space-x-4">
                                <div className="flex items-center space-x-2">
                                    <BarChart3 className="h-6 w-6 text-primary" />
                                    <h1 className="text-xl font-semibold text-foreground">Playwright Report Hub</h1>
                                </div>
                            </div>
                            <div className="flex items-center space-x-4">
                                <AppearanceToggleDropdown />
                                {auth.user ? (
                                    <Button asChild variant="default">
                                        <Link href={route('dashboard')}>
                                            Dashboard
                                        </Link>
                                    </Button>
                                ) : (
                                    <div className="flex items-center space-x-2">
                                        <Button asChild variant="ghost">
                                            <Link href={route('login')}>
                                                Log in
                                            </Link>
                                        </Button>
                                        <Button asChild variant="default">
                                            <Link href={route('register')}>
                                                Register
                                            </Link>
                                        </Button>
                                    </div>
                                )}
                            </div>
                        </nav>
                    </div>
                </header>
                
                <main className="container mx-auto px-4 py-8">
                    {/* Hero section */}
                    <div className="mb-12 text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground md:text-4xl mb-4">Test Reports Dashboard</h2>
                        <p className="mx-auto max-w-2xl text-muted-foreground">
                            Track, analyze and share your Playwright test results with your team. Get insights into your test performance over time.
                        </p>
                    </div>

                    {/* Stats overview */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Reports</CardTitle>
                                <FileText className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.totalReports}</div>
                                <p className="text-xs text-muted-foreground">All time</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Recent Reports</CardTitle>
                                <Clock className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.recentReports}</div>
                                <p className="text-xs text-muted-foreground">Last 7 days</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Tests</CardTitle>
                                <BarChart3 className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.totalTests.toLocaleString()}</div>
                                <p className="text-xs text-muted-foreground">All time</p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Avg Success Rate</CardTitle>
                                <CheckCircle className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.avgSuccessRate.toFixed(1)}%</div>
                                <p className="text-xs text-muted-foreground">All reports</p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Recent reports table */}
                    <Card className="mb-8">
                        <CardHeader>
                            <CardTitle>Recent Reports</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                {reports.length > 0 ? (
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-left font-medium p-2 pl-4">Report</th>
                                                <th className="text-left font-medium p-2">Platform</th>
                                                <th className="text-left font-medium p-2">Version</th>
                                                <th className="text-left font-medium p-2">Campaign</th>
                                                <th className="text-left font-medium p-2">Date</th>
                                                <th className="text-right font-medium p-2">Tests</th>
                                                <th className="text-right font-medium p-2">Pass Rate</th>
                                                <th className="text-right font-medium p-2 pr-4">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {reports.map((report) => (
                                                <tr key={report.id} className="border-b hover:bg-muted/50">
                                                    <td className="p-2 pl-4">
                                                        <div className="font-medium">{report.filename}</div>
                                                    </td>
                                                    <td className="p-2">
                                                        <Badge variant="outline" className="flex items-center gap-1">
                                                            <Monitor className="h-3 w-3" />
                                                            {report.platform}
                                                        </Badge>
                                                    </td>
                                                    <td className="p-2">{report.version}</td>
                                                    <td className="p-2">
                                                        <Badge variant="outline" className="flex items-center gap-1">
                                                            <Target className="h-3 w-3" />
                                                            {report.campaign}
                                                        </Badge>
                                                    </td>
                                                    <td className="p-2">
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                                                            <span>
                                                                {new Date(report.start_date).toLocaleDateString()}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="p-2 text-right">
                                                        {report.tests}
                                                    </td>
                                                    <td className="p-2 text-right">
                                                        {getSuccessRate(report.passes, report.tests)}%
                                                    </td>
                                                    <td className="p-2 pr-4 text-right">
                                                        <Badge className={getStatusColor(report.passes, report.failures, report.tests)}>
                                                            {report.failures === 0 ? (
                                                                <span className="flex items-center gap-1">
                                                                    <CheckCircle className="h-3 w-3" /> Pass
                                                                </span>
                                                            ) : report.failures / report.tests > 0.1 ? (
                                                                <span className="flex items-center gap-1">
                                                                    <XCircle className="h-3 w-3" /> Fail
                                                                </span>
                                                            ) : (
                                                                <span className="flex items-center gap-1">
                                                                    <AlertCircle className="h-3 w-3" /> Warning
                                                                </span>
                                                            )}
                                                        </Badge>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                ) : (
                                    <div className="text-center py-8 text-muted-foreground">
                                        <FileText className="mx-auto h-12 w-12 mb-4 opacity-50" />
                                        <p>No reports available</p>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </main>
                
                {/* Features section */}
                <section className="bg-muted py-12">
                    <div className="container mx-auto px-4">
                        <h2 className="text-2xl font-bold text-center mb-8">Key Features</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Card>
                                <CardHeader>
                                    <div className="bg-primary/10 w-10 h-10 rounded-lg flex items-center justify-center mb-2">
                                        <FileText className="h-5 w-5 text-primary" />
                                    </div>
                                    <CardTitle>Centralized Reporting</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground">
                                        Store and access all your Playwright test reports in one central location. Filter by platform, version, or campaign.
                                    </p>
                                </CardContent>
                            </Card>
                            
                            <Card>
                                <CardHeader>
                                    <div className="bg-primary/10 w-10 h-10 rounded-lg flex items-center justify-center mb-2">
                                        <BarChart3 className="h-5 w-5 text-primary" />
                                    </div>
                                    <CardTitle>Comprehensive Analytics</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground">
                                        Get insights into test performance trends, success rates, and failure patterns over time.
                                    </p>
                                </CardContent>
                            </Card>
                            
                            <Card>
                                <CardHeader>
                                    <div className="bg-primary/10 w-10 h-10 rounded-lg flex items-center justify-center mb-2">
                                        <Database className="h-5 w-5 text-primary" />
                                    </div>
                                    <CardTitle>Multiple Platform Support</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground">
                                        Track and compare tests across different browsers, environments, and database configurations.
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </section>
                
                {/* CTA Section */}
                <section className="py-16 container mx-auto px-4 text-center">
                    <div className="max-w-2xl mx-auto">
                        <h2 className="text-3xl font-bold mb-4">Ready to start tracking your tests?</h2>
                        <p className="text-xl text-muted-foreground mb-8">
                            Sign up today to access all features and start improving your testing workflow.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <Button asChild size="lg">
                                <Link href={route('register')}>
                                    Get started
                                </Link>
                            </Button>
                            <Button asChild variant="outline" size="lg">
                                <Link href={route('login')}>
                                    Sign in
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>
                
                {/* Footer */}
                <footer className="border-t py-8">
                    <div className="container mx-auto px-4">
                        <div className="flex flex-col md:flex-row justify-between items-center">
                            <div className="flex items-center space-x-2 mb-4 md:mb-0">
                                <BarChart3 className="h-5 w-5 text-primary" />
                                <span className="text-sm font-medium">Playwright Report Hub</span>
                            </div>
                            <div className="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-6 text-sm text-muted-foreground">
                                <span>© {new Date().getFullYear()} Playwright Report Hub. All rights reserved.</span>
                                <div className="flex items-center space-x-4">
                                    <Link href="#" className="hover:text-primary">Privacy</Link>
                                    <Link href="#" className="hover:text-primary">Terms</Link>
                                    <Link href="#" className="hover:text-primary">Help</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
