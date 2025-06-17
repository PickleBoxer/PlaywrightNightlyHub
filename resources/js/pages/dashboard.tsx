import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/react';
import { FileText, Upload, BarChart3, Clock } from 'lucide-react';

interface DashboardStats {
    totalReports: number;
    recentReports: number;
    totalTests: number;
    avgSuccessRate: number;
}

interface DashboardProps {
    stats: DashboardStats;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({ stats }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Dashboard"
                    description="Overview of your test reports and recent activity"
                />

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Reports</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats?.totalReports || 0}</div>
                            <p className="text-xs text-muted-foreground">All time</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Recent Reports</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats?.recentReports || 0}</div>
                            <p className="text-xs text-muted-foreground">Last 7 days</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Tests</CardTitle>
                            <BarChart3 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats?.totalTests?.toLocaleString() || 0}</div>
                            <p className="text-xs text-muted-foreground">All time</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Avg Success Rate</CardTitle>
                            <BarChart3 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats?.avgSuccessRate?.toFixed(1) || 0}%</div>
                            <p className="text-xs text-muted-foreground">Last 30 days</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Button
                                className="w-full justify-start"
                                variant="outline"
                                onClick={() => router.visit('/reports')}
                            >
                                <FileText className="mr-2 h-4 w-4" />
                                View All Reports
                            </Button>
                            <Button
                                className="w-full justify-start"
                                variant="outline"
                                onClick={() => router.visit('/upload')}
                            >
                                <Upload className="mr-2 h-4 w-4" />
                                Upload New Report
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Getting Started</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="text-sm text-muted-foreground">
                                <p>Welcome to your Playwright Report Hub! Here you can:</p>
                                <ul className="list-disc list-inside mt-2 space-y-1">
                                    <li>Upload and manage test reports</li>
                                    <li>View detailed test results and failures</li>
                                    <li>Track test performance over time</li>
                                    <li>Compare results between runs</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Activity */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-center py-8 text-muted-foreground">
                            <FileText className="mx-auto h-12 w-12 mb-4 opacity-50" />
                            <p>No recent activity</p>
                            <p className="text-sm">Upload your first report to get started</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
