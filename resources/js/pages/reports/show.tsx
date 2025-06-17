import { Head } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import {
    ChevronDown,
    ChevronRight,
    Clock,
    CheckCircle,
    XCircle,
    AlertCircle,
    Pause
} from 'lucide-react';

interface Test {
    id: number;
    title: string;
    state: string;
    duration: number;
    error_message?: string;
    stack_trace?: string;
}

interface DebugPanelProps {
    data: unknown;
}

interface TestSuite {
    id: number;
    title: string;
    campaign?: string;
    file?: string;
    duration: number;
    totalPasses: number;
    totalFailures: number;
    totalSkipped: number;
    totalPending: number;
    tests?: Test[];
    suites?: Record<string, TestSuite>;
}

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
    tests: number;
    passes: number;
    failures: number;
    pending: number;
    skipped: number;
    broken_since_last: number;
    fixed_since_last: number;
    equal_since_last: number;
    suites_data?: Record<string, TestSuite>;
}

interface ReportShowProps {
    report: Report;
}

// Add this component before the main ReportShow component
const DebugPanel = ({ data }: DebugPanelProps) => {
    return (
        <div className="fixed bottom-4 right-4 max-w-2xl max-h-[600px] overflow-auto bg-gray-900 text-white p-4 rounded-lg shadow-lg">
            <pre className="text-xs">
                {JSON.stringify(data, null, 2)}
            </pre>
        </div>
    );
};

export default function ReportShow({ report }: ReportShowProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedFilter, setSelectedFilter] = useState('all');
    const [expandedSuites, setExpandedSuites] = useState<Set<number>>(new Set());
    const { props } = usePage();
    const flash = props.flash as { success?: string; error?: string;}; // Type assertion for flash

    // Add this with other state declarations
    const [showDebug, setShowDebug] = useState(false);

    // Show success toast if flash message is present
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash]);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Reports',
            href: '/reports',
        },
        {
            title: `${report.version} - ${report.campaign}`,
            href: `/reports/${report.id}`,
        },
    ];

    const toggleSuite = (suiteId: number) => {
        const newExpanded = new Set(expandedSuites);
        if (newExpanded.has(suiteId)) {
            newExpanded.delete(suiteId);
        } else {
            newExpanded.add(suiteId);
        }
        setExpandedSuites(newExpanded);
    };

    const getTestIcon = (state: string) => {
        switch (state) {
            case 'passed':
                return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'failed':
                return <XCircle className="h-4 w-4 text-red-600" />;
            case 'skipped':
                return <Pause className="h-4 w-4 text-yellow-600" />;
            case 'pending':
                return <AlertCircle className="h-4 w-4 text-blue-600" />;
            default:
                return <Clock className="h-4 w-4 text-gray-600" />;
        }
    };    const getTestBadge = (state: string) => {
        const variants: Record<string, string> = {
            passed: 'bg-green-100 text-green-800',
            failed: 'bg-red-100 text-red-800',
            skipped: 'bg-yellow-100 text-yellow-800',
            pending: 'bg-blue-100 text-blue-800',
        };

        return <Badge className={variants[state] || 'bg-gray-100 text-gray-800'}>{state}</Badge>;
    };

    const formatDuration = (milliseconds: number) => {
        if (milliseconds < 1000) return `${milliseconds}ms`;
        const seconds = Math.floor(milliseconds / 1000);
        const minutes = Math.floor(seconds / 60);
        if (minutes > 0) {
            return `${minutes}m ${seconds % 60}s`;
        }
        return `${seconds}s`;
    };

    const filterTests = (tests: Test[]) => {
        return tests.filter(test => {
            const matchesSearch = !searchTerm ||
                test.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (test.error_message && test.error_message.toLowerCase().includes(searchTerm.toLowerCase()));

            const matchesFilter = selectedFilter === 'all' || test.state === selectedFilter;

            return matchesSearch && matchesFilter;
        });
    };

    const renderSuite = (suite: TestSuite, level = 0) => {
        const filteredTests = filterTests(suite.tests || []);
        const hasVisibleContent = filteredTests.length > 0 || (suite.suites && Object.keys(suite.suites).length > 0);

        if (!hasVisibleContent) return null;

        const isExpanded = expandedSuites.has(suite.id);
        const paddingLeft = level * 20;

        return (
            <div key={suite.id} className="border rounded-lg" style={{ marginLeft: paddingLeft }}>
                <Collapsible open={isExpanded} onOpenChange={() => toggleSuite(suite.id)}>
                    <CollapsibleTrigger asChild>
                        <div className="flex items-center justify-between p-4 hover:bg-accent border rounded-lg cursor-pointer">
                            <div className="flex items-center gap-2">
                                {isExpanded ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                                <h3 className="font-medium">{suite.title}</h3>
                                {suite.file && (
                                    <span className="text-sm text-gray-500">({suite.file})</span>
                                )}
                            </div>
                            <div className="flex items-center gap-4 text-sm">
                                <div className="flex gap-2">
                                    {suite.totalPasses > 0 && (
                                        <Badge className="bg-green-100 text-green-800">{suite.totalPasses} passed</Badge>
                                    )}
                                    {suite.totalFailures > 0 && (
                                        <Badge className="bg-red-100 text-red-800">{suite.totalFailures} failed</Badge>
                                    )}
                                    {suite.totalSkipped > 0 && (
                                        <Badge className="bg-yellow-100 text-yellow-800">{suite.totalSkipped} skipped</Badge>
                                    )}
                                    {suite.totalPending > 0 && (
                                        <Badge className="bg-blue-100 text-blue-800">{suite.totalPending} pending</Badge>
                                    )}
                                </div>
                                <span className="text-gray-500">{formatDuration(suite.duration)}</span>
                            </div>
                        </div>
                    </CollapsibleTrigger>

                    <CollapsibleContent>
                        <div className="px-4 pb-4 space-y-2">
                            {/* Render child suites */}
                            {suite.suites && Object.values(suite.suites).map(childSuite =>
                                renderSuite(childSuite, level + 1)
                            )}

                            {/* Render tests */}
                            {filteredTests.map(test => (
                                <div key={test.id} className="border-l-2 border-gray-200 pl-4 py-2">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            {getTestIcon(test.state)}
                                            <span className="font-medium">{test.title}</span>
                                            {getTestBadge(test.state)}
                                        </div>
                                        <span className="text-sm text-gray-500">{formatDuration(test.duration)}</span>
                                    </div>

                                    {test.error_message && (
                                        <div className="mt-2 p-3 bg-red-50 border border-red-200 rounded">
                                            <p className="text-sm text-red-800 font-medium">Error:</p>
                                            <p className="text-sm text-red-700 mt-1">{test.error_message}</p>
                                            {test.stack_trace && (
                                                <details className="mt-2">
                                                    <summary className="text-sm text-red-600 cursor-pointer">Stack trace</summary>
                                                    <pre className="text-xs text-red-600 mt-2 overflow-x-auto">
                                                        {test.stack_trace}
                                                    </pre>
                                                </details>
                                            )}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </CollapsibleContent>
                </Collapsible>
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Report - ${report.version} - ${report.campaign}`} />

            <div className="space-y-6 px-4 py-6">
                <div className="flex items-center justify-between">
                    <Heading
                        title={`${report.version} - ${report.campaign}`}
                        description={`Test report from ${report.date}`}
                    />
                    <Button
                        variant="outline"
                        onClick={() => router.visit('/reports')}
                    >
                        Back to Reports
                    </Button>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <div className="text-2xl font-bold">{report.tests}</div>
                                <div className="text-sm text-gray-500">Total Tests</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-green-600">{report.passes}</div>
                                <div className="text-sm text-gray-500">Passed</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-red-600">{report.failures}</div>
                                <div className="text-sm text-gray-500">Failed</div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-center">
                                <div className="text-2xl font-bold">{formatDuration(report.duration)}</div>
                                <div className="text-sm text-gray-500">Duration</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex gap-4">
                            <div className="flex-1">
                                <Input
                                    placeholder="Search tests..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="w-full"
                                />
                            </div>
                            <div className="flex gap-2">
                                {['all', 'passed', 'failed', 'skipped', 'pending'].map(filter => (
                                    <Button
                                        key={filter}
                                        variant={selectedFilter === filter ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => setSelectedFilter(filter)}
                                    >
                                        {filter}
                                    </Button>
                                ))}
                            </div>
                            <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setShowDebug(!showDebug)}
                        >
                            {showDebug ? 'Hide Debug' : 'Show Debug'}
                        </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Test Suites */}
                <div className="space-y-4">
                    {report.suites_data && Object.values(report.suites_data).map(suite => renderSuite(suite))}
                </div>

                {(!report.suites_data || Object.keys(report.suites_data).length === 0) && (
                    <Card>
                        <CardContent className="text-center py-12">
                            <h3 className="text-lg font-medium text-gray-900 mb-2">No test suites found</h3>
                            <p className="text-gray-500">This report doesn't contain any test suites.</p>
                        </CardContent>
                    </Card>
                )}
            </div>
            {showDebug && <DebugPanel data={report} />}
        </AppLayout>
    );
}
