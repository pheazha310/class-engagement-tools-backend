<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckSquare,
    Star,
    Trophy,
    GraduationCap,
    ArrowRight,
    Clock,
    CheckCircle2,
    User,
    Mail,
    Shield,
    School,
    MapPin,
    Users,
    ChartLine,
} from '@lucide/vue';
import { send } from '@/routes/verification';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { getInitials } from '@/composables/useInitials';

const props = defineProps<{
    student: {
        name: string;
        email: string;
        role: string;
        school: string;
        province: string;
        email_verified: boolean;
    };
    classes: Array<{
        id: number;
        title: string;
        teacher: string;
        students: number;
        progress: number;
    }>;
    activities: Array<{
        id: number;
        description: string;
        time: string;
        type: string;
    }>;
    leaderboard: Array<{
        rank: number;
        name: string;
        points: number;
        avatar: string | null;
    }>;
    statistics: Array<{
        title: string;
        value: string | number;
        icon: string;
    }>;
}>();

const iconMap: Record<string, object> = {
    BookOpen,
    CheckSquare,
    Star,
    Trophy,
};

const activityIcons: Record<string, object> = {
    task: CheckCircle2,
    class: GraduationCap,
    points: Star,
};

const rankEmojis: Record<number, string> = {
    1: '\u{1F947}',
    2: '\u{1F948}',
    3: '\u{1F949}',
};

const progressColor = (progress: number): string => {
    if (progress >= 75) return 'bg-green-500';
    if (progress >= 50) return 'bg-blue-500';
    if (progress >= 25) return 'bg-yellow-500';
    return 'bg-gray-300';
};

const resendVerification = () => {
    router.post(send.url());
};
</script>

<template>
    <Head title="Student Dashboard" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Email Verification Banner -->
        <div
            v-if="!student.email_verified"
            class="flex items-center gap-3 rounded-xl border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800/30 dark:bg-yellow-950/20 dark:text-yellow-200"
        >
            <Mail class="h-5 w-5 shrink-0 text-yellow-500" />
            <p class="flex-1">
                Please check your email to verify your account.
                <span class="block text-xs text-yellow-600 dark:text-yellow-400">
                    Didn't receive the email? Check your spam folder or
                    <a
                        href="#"
                        class="underline hover:no-underline"
                        @click.prevent="resendVerification"
                    >click here to resend</a>.
                </span>
            </p>
        </div>

        <!-- Welcome Hero -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700 p-6 md:p-8"
        >
            <div
                class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyek0zNiAyNHYySDI0di0yaDEyeiIvPjwvZz48L2c+PC9zdmc+')] opacity-30"
            />
            <div
                class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl"
            />
            <div
                class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-purple-300/20 blur-3xl"
            />

            <div
                class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Avatar class="h-16 w-16 border-2 border-white/30 ring-2 ring-white/20 md:h-20 md:w-20">
                        <AvatarFallback class="bg-white/20 text-2xl text-white">
                            {{ getInitials(student.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="text-white">
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                            Welcome back, {{ student.name }}
                            <span class="inline-block">👋</span>
                        </h1>
                        <p class="mt-1 text-white/80">
                            Continue your learning journey and stay engaged with your classes.
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <Button
                        variant="secondary"
                        class="bg-white/20 text-white hover:bg-white/30"
                    >
                        <GraduationCap class="mr-1.5 h-4 w-4" />
                        Join Class
                    </Button>
                    <Button
                        variant="secondary"
                        class="bg-white/20 text-white hover:bg-white/30"
                    >
                        <ChartLine class="mr-1.5 h-4 w-4" />
                        View Tasks
                    </Button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <Card
                v-for="stat in statistics"
                :key="stat.title"
                class="border-l-4 transition-shadow hover:shadow-md"
                :class="{
                    'border-l-blue-500': stat.icon === 'BookOpen',
                    'border-l-green-500': stat.icon === 'CheckSquare',
                    'border-l-yellow-500': stat.icon === 'Star',
                    'border-l-purple-500': stat.icon === 'Trophy',
                }"
            >
                <CardHeader class="flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ stat.title }}
                    </CardTitle>
                    <component
                        :is="iconMap[stat.icon]"
                        class="h-5 w-5"
                        :class="{
                            'text-blue-500': stat.icon === 'BookOpen',
                            'text-green-500': stat.icon === 'CheckSquare',
                            'text-yellow-500': stat.icon === 'Star',
                            'text-purple-500': stat.icon === 'Trophy',
                        }"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold tracking-tight">
                        {{ stat.value }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column: Classes + Activities + Leaderboard -->
            <div class="flex flex-col gap-6 lg:col-span-2">
                <!-- My Classes -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5 text-blue-500" />
                            My Classes
                        </CardTitle>
                        <CardDescription>
                            You are enrolled in {{ classes.length }} classes
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-for="cls in classes"
                            :key="cls.id"
                            class="rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="flex-1 space-y-2">
                                    <h3 class="font-semibold">
                                        {{ cls.title }}
                                    </h3>
                                    <div
                                        class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted-foreground"
                                    >
                                        <span class="flex items-center gap-1">
                                            <User class="h-3.5 w-3.5" />
                                            {{ cls.teacher }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Users class="h-3.5 w-3.5" />
                                            {{ cls.students }} students
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-2 flex-1 overflow-hidden rounded-full bg-muted"
                                        >
                                            <div
                                                :class="progressColor(cls.progress)"
                                                class="h-full rounded-full transition-all"
                                                :style="{ width: cls.progress + '%' }"
                                            />
                                        </div>
                                        <span class="text-xs font-medium text-muted-foreground">
                                            {{ cls.progress }}%
                                        </span>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" class="shrink-0">
                                    View Class
                                    <ArrowRight class="ml-1 h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activities -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-blue-500" />
                            Recent Activities
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-0">
                            <li
                                v-for="(activity, index) in activities"
                                :key="activity.id"
                            >
                                <div class="flex gap-4 pb-4">
                                    <div
                                        class="relative flex flex-col items-center"
                                    >
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full border bg-background"
                                            :class="{
                                                'border-green-200 text-green-600':
                                                    activity.type === 'task',
                                                'border-blue-200 text-blue-600':
                                                    activity.type === 'class',
                                                'border-yellow-200 text-yellow-600':
                                                    activity.type === 'points',
                                            }"
                                        >
                                            <component
                                                :is="activityIcons[activity.type] || CheckCircle2"
                                                class="h-4 w-4"
                                            />
                                        </div>
                                        <div
                                            v-if="index < activities.length - 1"
                                            class="mt-1 h-full w-px bg-border"
                                        />
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <p class="text-sm font-medium">
                                            {{ activity.description }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ activity.time }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Leaderboard -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Trophy class="h-5 w-5 text-yellow-500" />
                            Leaderboard
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b text-muted-foreground">
                                        <th class="pb-2 font-medium">Rank</th>
                                        <th class="pb-2 font-medium">Student</th>
                                        <th class="pb-2 text-right font-medium">
                                            Points
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="entry in leaderboard"
                                        :key="entry.rank"
                                        class="border-b last:border-0"
                                        :class="{
                                            'bg-yellow-50/50 dark:bg-yellow-950/10':
                                                entry.rank === 1,
                                        }"
                                    >
                                        <td class="py-3">
                                            <span
                                                v-if="rankEmojis[entry.rank]"
                                                class="text-lg"
                                            >
                                                {{ rankEmojis[entry.rank] }}
                                            </span>
                                            <span
                                                v-else
                                                class="font-medium text-muted-foreground"
                                            >
                                                #{{ entry.rank }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Avatar
                                                    class="h-7 w-7"
                                                >
                                                    <AvatarFallback
                                                        class="text-xs"
                                                    >
                                                        {{ getInitials(entry.name) }}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <span
                                                    class="font-medium"
                                                    :class="{
                                                        'text-yellow-600 dark:text-yellow-400':
                                                            entry.rank === 1,
                                                    }"
                                                >
                                                    {{ entry.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 text-right font-semibold"
                                        >
                                            {{ entry.points.toLocaleString() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Right Column: Student Profile Sidebar -->
            <div class="flex flex-col gap-6">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-2">
                            <Avatar class="h-20 w-20 border-2 border-primary/10">
                                <AvatarFallback class="bg-primary/5 text-2xl">
                                    {{ getInitials(student.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </div>
                        <CardTitle>{{ student.name }}</CardTitle>
                        <CardDescription>{{ student.email }}</CardDescription>
                        <Badge variant="secondary" class="mt-1">
                            {{ student.role }}
                        </Badge>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Separator />
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                >
                                    <Mail class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Email
                                    </p>
                                    <p class="font-medium">
                                        {{ student.email }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                >
                                    <Shield class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Role
                                    </p>
                                    <p class="font-medium">
                                        {{ student.role }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400"
                                >
                                    <School class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        School
                                    </p>
                                    <p class="font-medium">
                                        {{ student.school }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400"
                                >
                                    <MapPin class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Province
                                    </p>
                                    <p class="font-medium">
                                        {{ student.province }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button variant="outline" class="w-full" size="sm">
                            <User class="mr-1.5 h-4 w-4" />
                            View Full Profile
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </div>
</template>
