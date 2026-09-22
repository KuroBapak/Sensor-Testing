import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Sliders, RotateCcw, Save } from 'lucide-react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { FormEventHandler } from 'react';

interface SensitivityProps {
    sensitivity: {
        browser_tank_threshold: number;
        fuel_tanker_threshold: number;
        main_tank_threshold: number;
        tolerance_pct: number;
    };
}

export default function SensitivityIndex({ sensitivity }: SensitivityProps) {
    const { data, setData, post, processing, errors, reset } = useForm(sensitivity);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/admin/sensitivity');
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <Head title="Alert Sensitivity" />
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Alert Sensitivity</h1>
                    <p className="text-sm text-muted-foreground">
                        Configure threshold modes for Theft Detection and Transfer Reconciliation.
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button type="button" variant="outline" size="sm" onClick={() => reset()}>
                        <RotateCcw className="mr-2 h-4 w-4" /> Reset
                    </Button>
                    <Button type="submit" size="sm" disabled={processing}>
                        <Save className="mr-2 h-4 w-4" /> Save
                    </Button>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Sliders className="h-5 w-5" /> C1: Sudden Change / Theft
                        </CardTitle>
                        <CardDescription>
                            Theft threshold per tank division (% of capacity).
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="b_thresh">Browser Tank Threshold (%)</Label>
                            <Input
                                id="b_thresh"
                                type="number"
                                step="0.1"
                                value={data.browser_tank_threshold}
                                onChange={(e) => setData('browser_tank_threshold', Number(e.target.value))}
                            />
                            {errors.browser_tank_threshold && <p className="text-sm text-red-500">{errors.browser_tank_threshold}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="t_thresh">Fuel Tanker Threshold (%)</Label>
                            <Input
                                id="t_thresh"
                                type="number"
                                step="0.1"
                                value={data.fuel_tanker_threshold}
                                onChange={(e) => setData('fuel_tanker_threshold', Number(e.target.value))}
                            />
                            {errors.fuel_tanker_threshold && <p className="text-sm text-red-500">{errors.fuel_tanker_threshold}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="m_thresh">Main Tank Threshold (%)</Label>
                            <Input
                                id="m_thresh"
                                type="number"
                                step="0.1"
                                value={data.main_tank_threshold}
                                onChange={(e) => setData('main_tank_threshold', Number(e.target.value))}
                            />
                            {errors.main_tank_threshold && <p className="text-sm text-red-500">{errors.main_tank_threshold}</p>}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Sliders className="h-5 w-5" /> C2: Transfer Reconciliation
                        </CardTitle>
                        <CardDescription>
                            Tolerance between ATG change and line flow-meter liters.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="tol">Tolerance (% difference)</Label>
                            <Input
                                id="tol"
                                type="number"
                                step="0.01"
                                value={data.tolerance_pct}
                                onChange={(e) => setData('tolerance_pct', Number(e.target.value))}
                            />
                            {errors.tolerance_pct && <p className="text-sm text-red-500">{errors.tolerance_pct}</p>}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </form>
    );
}

SensitivityIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Alert Sensitivity', href: '/admin/sensitivity' },
    ],
};
