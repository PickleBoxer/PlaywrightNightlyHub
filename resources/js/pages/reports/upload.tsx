import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { useState } from 'react';
import { UploadCloud } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { toast } from 'sonner';

interface UploadProps {
    platforms: string[];
    databases: string[];
    campaigns: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Upload Report',
        href: '/upload',
    },
];

export default function Upload({ platforms, databases, campaigns }: UploadProps) {
    const [dragging, setDragging] = useState(false);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);

    const form = useForm({
        report: null as File | null,
        platform: platforms[0],
        database: databases[0],
        campaign: campaigns[0],
        version: '',
        force: false as boolean
    });

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            setSelectedFile(file);
            form.setData('report', file);
        }
    };

    const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setDragging(false);
    };

    const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setDragging(false);

        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            const file = files[0];
            if (file.type === 'application/json') {
                setSelectedFile(file);
                form.setData('report', file);
            } else {
                toast.error('Please upload a JSON file');
            }
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post('/upload', {
            onError: (errors) => {
                console.error(errors);
                toast.error(errors.error);
            },
            onSuccess: () => {
                toast.success('Report uploaded successfully');
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Upload Report" />

            <div className="space-y-6 px-4 py-6">
                <Heading
                    title="Upload Report"
                    description="Upload a new Playwright test report"
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Upload Report File</CardTitle>
                        <CardDescription>
                            Upload a Playwright JSON report file to process and store in the system.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label>Report File</Label>
                                <div
                                    className={`border-2 border-dashed rounded-md p-10 text-center ${dragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'}`}
                                    onDragOver={handleDragOver}
                                    onDragLeave={handleDragLeave}
                                    onDrop={handleDrop}
                                >
                                    <div className="flex flex-col items-center justify-center space-y-3">
                                        <UploadCloud className="h-10 w-10 text-muted-foreground" />
                                        <p className="text-sm font-medium">
                                            Drag and drop your JSON report file here or click to browse
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            JSON files only, max size 10MB
                                        </p>
                                        {selectedFile && (
                                            <div className="mt-2 text-sm font-medium text-primary">
                                                Selected: {selectedFile.name}
                                            </div>
                                        )}
                                        <Input
                                            type="file"
                                            accept=".json,application/json"
                                            className="hidden"
                                            onChange={handleFileChange}
                                            id="report-file"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => document.getElementById('report-file')?.click()}
                                        >
                                            Browse Files
                                        </Button>

                                        {form.errors.report && (
                                            <p className="text-red-500 text-xs mt-1">{form.errors.report}</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="platform">Platform</Label>
                                    <Select
                                        defaultValue={form.data.platform}
                                        onValueChange={(value) => form.setData('platform', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select platform" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {platforms.map((platform) => (
                                                <SelectItem key={platform} value={platform}>
                                                    {platform}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {form.errors.platform && (
                                        <p className="text-red-500 text-xs mt-1">{form.errors.platform}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="database">Database</Label>
                                    <Select
                                        defaultValue={form.data.database}
                                        onValueChange={(value) => form.setData('database', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select database" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {databases.map((database) => (
                                                <SelectItem key={database} value={database}>
                                                    {database}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {form.errors.database && (
                                        <p className="text-red-500 text-xs mt-1">{form.errors.database}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="campaign">Campaign</Label>
                                    <Select
                                        defaultValue={form.data.campaign}
                                        onValueChange={(value) => form.setData('campaign', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select campaign" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {campaigns.map((campaign) => (
                                                <SelectItem key={campaign} value={campaign}>
                                                    {campaign}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {form.errors.campaign && (
                                        <p className="text-red-500 text-xs mt-1">{form.errors.campaign}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="version">Version (optional)</Label>
                                <p className="text-xs text-muted-foreground mb-2">
                                    If left empty, will extract from filename or use default
                                </p>
                                <Input
                                    id="version"
                                    type="text"
                                    placeholder="e.g., 1.7.8"
                                    value={form.data.version}
                                    onChange={(e) => form.setData('version', e.target.value)}
                                />
                                {form.errors.version && (
                                    <p className="text-red-500 text-xs mt-1">{form.errors.version}</p>
                                )}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="force"
                                    checked={form.data.force}
                                    onCheckedChange={(checked) => form.setData('force', checked as boolean)}
                                />
                                <Label htmlFor="force" className="text-sm font-normal">
                                    Force upload (override duplicate detection)
                                </Label>
                            </div>

                            <div className="flex justify-end">
                                <Button
                                    type="submit"
                                    disabled={form.processing || !selectedFile}
                                >
                                    {form.processing ? 'Uploading...' : 'Upload Report'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
