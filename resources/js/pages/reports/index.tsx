import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Download, Eye, Filter, Search } from 'lucide-react';
import { useState } from 'react';

interface Report {
    id: number;
    date: string;
    version: string;
    campaign: string;
    platform: string;
    database: string;
    start_date: string;
    end_date: string;
    duration: number;
    suites: number;
    tests: {
        total: number;
        passed: number;
        failed: number;
        pending: number;
        skipped: number;
    };
    broken_since_last: number;
    fixed_since_last: number;
    equal_since_last: number;
    download?: string;
}

interface ReportsIndexProps {
    reports: {
        data: Report[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        platform?: string;
        campaign?: string;
        version?: string;
        search?: string;
    };
    platforms: string[];
    campaigns: string[];
    versions: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '/reports',
    },
];

export default function ReportsIndex({ reports, filters, platforms = [], campaigns = [], versions = [] }: ReportsIndexProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedPlatform, setSelectedPlatform] = useState(filters.platform || 'all');
    const [selectedCampaign, setSelectedCampaign] = useState(filters.campaign || 'all');
    const [selectedVersion, setSelectedVersion] = useState(filters.version || 'all');

    const handleSearch = () => {
        router.get(
            '/reports',
            {
                search: searchTerm === '' ? undefined : searchTerm,
                platform: selectedPlatform === 'all' ? undefined : selectedPlatform,
                campaign: selectedCampaign === 'all' ? undefined : selectedCampaign,
                version: selectedVersion === 'all' ? undefined : selectedVersion,
                page: 1,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleReset = () => {
        setSearchTerm('');
        setSelectedPlatform('all');
        setSelectedCampaign('all');
        setSelectedVersion('all');
        router.get(
            '/reports',
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handlePageChange = (page: number) => {
        router.get(
            '/reports',
            {
                ...filters,
                page,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const getStatusBadge = (report: Report) => {
        const { failed, total } = report.tests;
        const failureRate = total > 0 ? (failed / total) * 100 : 0;

        if (failureRate === 0) {
            return <Badge className="bg-green-100 text-green-800">Passed</Badge>;
        } else if (failureRate < 10) {
            return <Badge className="bg-yellow-100 text-yellow-800">Warning</Badge>;
        } else {
            return <Badge className="bg-red-100 text-red-800">Failed</Badge>;
        }
    };

    const formatDuration = (milliseconds: number) => {
        const minutes = Math.floor(milliseconds / 60000);
        const seconds = Math.floor((milliseconds % 60000) / 1000);
        return `${minutes}m ${seconds}s`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Test Reports" />

            <div className="space-y-6 px-4 py-6">
                <Heading title="Test Reports" description="View and manage your test execution reports" />

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-4 w-4" />
                            Filters
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div className="relative">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                <Input
                                    placeholder="Search reports..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-10"
                                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                />
                            </div>

                            <Select value={selectedPlatform} onValueChange={setSelectedPlatform}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Platform" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Platforms</SelectItem>
                                    {platforms.map((platform) => (
                                        <SelectItem key={platform} value={platform}>
                                            {platform}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedCampaign} onValueChange={setSelectedCampaign}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Campaign" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Campaigns</SelectItem>
                                    {campaigns.map((campaign) => (
                                        <SelectItem key={campaign} value={campaign}>
                                            {campaign}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedVersion} onValueChange={setSelectedVersion}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Version" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Versions</SelectItem>
                                    {versions.map((version) => (
                                        <SelectItem key={version} value={version}>
                                            {version}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <div className="flex gap-2">
                                <Button onClick={handleSearch} className="flex-1">
                                    Search
                                </Button>
                                <Button variant="outline" onClick={handleReset}>
                                    Reset
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Reports List */}
                <div className="space-y-4">
                    {reports.data.map((report) => (
                        <Card key={report.id}>
                            <CardContent className="p-6">
                                <div className="flex flex-col gap-4 justify-between">
                                    <div className="flex gap-4">
                                        <h3 className="text-lg font-semibold">
                                            {report.version} - {report.campaign}
                                        </h3>
                                        {getStatusBadge(report)}
                                    </div>
                                    <div className="flex items-center justify-between gap-4">
                                        <div className="flex-1 space-y-2">
                                            <div className="grid grid-cols-2 gap-4 text-sm text-gray-600 md:grid-cols-4">
                                                <div>
                                                    <span className="font-medium">Platform:</span> {report.platform}
                                                </div>
                                                <div>
                                                    <span className="font-medium">Database:</span> {report.database}
                                                </div>
                                                <div>
                                                    <span className="font-medium">Date:</span> {report.date}
                                                </div>
                                                <div>
                                                    <span className="font-medium">Duration:</span> {formatDuration(report.duration)}
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-4 text-sm md:grid-cols-5">
                                                <div className="rounded bg-gray-50 p-2 text-center">
                                                    <div className="font-medium text-gray-900">{report.tests.total}</div>
                                                    <div className="text-gray-500">Total</div>
                                                </div>
                                                <div className="rounded bg-green-50 p-2 text-center">
                                                    <div className="font-medium text-green-800">{report.tests.passed}</div>
                                                    <div className="text-green-600">Passed</div>
                                                </div>
                                                <div className="rounded bg-red-50 p-2 text-center">
                                                    <div className="font-medium text-red-800">{report.tests.failed}</div>
                                                    <div className="text-red-600">Failed</div>
                                                </div>
                                                <div className="rounded bg-yellow-50 p-2 text-center">
                                                    <div className="font-medium text-yellow-800">{report.tests.skipped}</div>
                                                    <div className="text-yellow-600">Skipped</div>
                                                </div>
                                                <div className="rounded bg-blue-50 p-2 text-center">
                                                    <div className="font-medium text-blue-800">{report.tests.pending}</div>
                                                    <div className="text-blue-600">Pending</div>
                                                </div>
                                            </div>

                                            {(report.broken_since_last > 0 || report.fixed_since_last > 0) && (
                                                <div className="flex gap-4 text-sm">
                                                    {report.broken_since_last > 0 && (
                                                        <div className="text-red-600">↑ {report.broken_since_last} broken since last</div>
                                                    )}
                                                    {report.fixed_since_last > 0 && (
                                                        <div className="text-green-600">↓ {report.fixed_since_last} fixed since last</div>
                                                    )}
                                                </div>
                                            )}
                                        </div>

                                        <div className="flex gap-2">
                                            <Button variant="outline" size="sm" onClick={() => router.visit(`/reports/${report.id}`)}>
                                                <Eye className="mr-1 h-4 w-4" />
                                                View
                                            </Button>
                                            {report.download && (
                                                <Button variant="outline" size="sm" onClick={() => window.open(report.download, '_blank')}>
                                                    <Download className="mr-1 h-4 w-4" />
                                                    Download
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Pagination */}
                {reports.last_page > 1 && (
                    <div className="flex justify-center gap-2">
                        <Button variant="outline" disabled={reports.current_page === 1} onClick={() => handlePageChange(reports.current_page - 1)}>
                            Previous
                        </Button>

                        {Array.from({ length: Math.min(5, reports.last_page) }, (_, i) => {
                            const page = reports.current_page <= 3 ? i + 1 : reports.current_page + i - 2;

                            if (page > reports.last_page) return null;

                            return (
                                <Button
                                    key={page}
                                    variant={page === reports.current_page ? 'default' : 'outline'}
                                    onClick={() => handlePageChange(page)}
                                >
                                    {page}
                                </Button>
                            );
                        })}

                        <Button
                            variant="outline"
                            disabled={reports.current_page === reports.last_page}
                            onClick={() => handlePageChange(reports.current_page + 1)}
                        >
                            Next
                        </Button>
                    </div>
                )}

                {/* Empty State */}
                {reports.data.length === 0 && (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <h3 className="mb-2 text-lg font-medium text-gray-900">No reports found</h3>
                            <p className="text-gray-500">Try adjusting your search criteria or upload a new report.</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
